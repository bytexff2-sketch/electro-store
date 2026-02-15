<?php
/**
 * Shopping Cart API
 * Handles cart operations (add, remove, update, view)
 */

require_once '../config.php';
require_once '../utils.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    sendError('Not logged in', 401);

}

try {
    $action = isset($_GET['action']) ? $_GET['action'] : '';

    switch ($action) {
        case 'view':
            viewCart($mysqli);
            break;
        case 'add':
            addToCart($mysqli);
            break;
        case 'remove':
            removeFromCart($mysqli);
            break;
        case 'update':
            updateCartQuantity($mysqli);
            break;
        case 'clear':
            clearCart($mysqli);
            break;
        default:
            sendError('Invalid action', 400);
    }
} catch (Exception $e) {
    error_log('Cart API error: ' . $e->getMessage());
    $msg = $e->getMessage();
    
    // Handle specific database errors
    if (strpos($msg, 'password_hash') !== false || strpos($msg, 'Unknown column') !== false) {
        sendError('Database schema issue. Please run SETUP_DATABASE.html', 500);
    } else if (strpos($msg, 'foreign key') !== false || strpos($msg, 'FOREIGN KEY') !== false) {
        // User session is invalid - user doesn't exist in database
        sendError('Your session has expired. Please login again.', 401);
    } else {
        sendError('Server error: ' . $msg, 500);
    }
}

/**
 * View shopping cart
 */
function viewCart($mysqli) {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendError('Invalid request method', 405);
    }

    try {
        $user_id = $_SESSION['user_id'];
        
        // Verify user exists in database
        if (!userExists($mysqli, $user_id)) {
            error_log('Invalid user_id ' . $user_id . ' in session');
            sendError('User session is invalid. Please login again.', 401);
        }

        $query = "SELECT c.cart_id, c.quantity, p.product_id, p.product_name, p.price, p.image_url, p.stock_quantity
                  FROM shopping_cart c
                  JOIN products p ON c.product_id = p.product_id
                  WHERE c.user_id = ?
                  ORDER BY c.added_at DESC";

        $stmt = $mysqli->prepare($query);
        
        if (!$stmt) {
            error_log('Cart view prepare failed: ' . $mysqli->error);
            sendError('Failed to prepare cart query: ' . $mysqli->error, 500);
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cartItems = $result->fetch_all(MYSQLI_ASSOC);

        if (!$cartItems) {
            $cartItems = [];
        }

        $subtotal = 0;
        $tax = 0;
        $total = 0;

        // Convert numeric fields and calculate subtotals
        foreach ($cartItems as &$item) {
            // Cast to proper types
            $item['cart_id'] = (int)$item['cart_id'];
            $item['product_id'] = (int)$item['product_id'];
            $item['quantity'] = (int)$item['quantity'];
            $item['price'] = (float)$item['price'];
            $item['stock_quantity'] = (int)$item['stock_quantity'];
            
            $item['subtotal'] = round($item['price'] * $item['quantity'], 2);
            $subtotal += $item['subtotal'];
        }

        $tax = round($subtotal * 0.08, 2); // 8% tax
        $total = round($subtotal + $tax, 2);

        $stmt->close();

        sendSuccess([
            'items' => $cartItems,
            'item_count' => count($cartItems),
            'subtotal' => round($subtotal, 2),
            'tax' => $tax,
            'total' => $total
        ], 'Cart retrieved', 200);
    } catch (Exception $e) {
        error_log('viewCart exception: ' . $e->getMessage());
        sendError('Error retrieving cart: ' . $e->getMessage(), 500);
    }
}

/**
 * Add item to cart
 */
function addToCart($mysqli) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendError('Invalid request method', 405);
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['product_id'])) {
        sendError('Product ID is required', 400);
    }

    $user_id = $_SESSION['user_id'];
    
    // Verify user exists in database before proceeding
    if (!userExists($mysqli, $user_id)) {
        error_log('Invalid user_id ' . $user_id . ' in session');
        sendError('User session is invalid. Please login again.', 401);
    }
    
    $product_id = (int)$data['product_id'];
    $quantity = (int)($data['quantity'] ?? 1);

    if ($quantity < 1) {
        sendError('Quantity must be at least 1', 400);
    }

    // Check if product exists and is in stock
    $stmt = $mysqli->prepare("SELECT stock_quantity FROM products WHERE product_id = ? AND is_active = 1");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        sendError('Product not found', 404);
    }

    $product = $result->fetch_assoc();
    $stmt->close();

    if ($product['stock_quantity'] < $quantity) {
        sendError('Insufficient stock available', 400);
    }

    // Check if item already in cart
    $checkStmt = $mysqli->prepare("SELECT cart_id, quantity FROM shopping_cart WHERE user_id = ? AND product_id = ?");
    $checkStmt->bind_param("ii", $user_id, $product_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // Update quantity if already in cart
        $cartItem = $checkResult->fetch_assoc();
        $newQuantity = $cartItem['quantity'] + $quantity;

        if ($newQuantity > $product['stock_quantity']) {
            sendError('Insufficient stock available', 400);
        }

        $updateStmt = $mysqli->prepare("UPDATE shopping_cart SET quantity = ? WHERE cart_id = ?");
        $updateStmt->bind_param("ii", $newQuantity, $cartItem['cart_id']);
        $updateStmt->execute();
        $updateStmt->close();
    } else {
        // Add new item to cart
        $insertStmt = $mysqli->prepare("INSERT INTO shopping_cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $insertStmt->bind_param("iii", $user_id, $product_id, $quantity);
        $insertStmt->execute();
        $insertStmt->close();
    }

    $checkStmt->close();

    sendSuccess(null, 'Product added to cart', 201);
}

/**
 * Remove item from cart
 */
function removeFromCart($mysqli) {
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendError('Invalid request method', 405);
    }

    if (!isset($_GET['id'])) {
        sendError('Cart ID is required', 400);
    }

    $user_id = $_SESSION['user_id'];
    $cart_id = (int)$_GET['id'];

    $stmt = $mysqli->prepare("DELETE FROM shopping_cart WHERE cart_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        sendSuccess(null, 'Item removed from cart', 200);
    } else {
        sendError('Cart item not found', 404);
    }
    $stmt->close();
}

/**
 * Update cart item quantity
 */
function updateCartQuantity($mysqli) {
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendError('Invalid request method', 405);
    }

    if (!isset($_GET['id'])) {
        sendError('Cart ID is required', 400);
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['quantity'])) {
        sendError('Quantity is required', 400);
    }

    $user_id = $_SESSION['user_id'];
    $cart_id = (int)$_GET['id'];
    $quantity = (int)$data['quantity'];

    if ($quantity < 1) {
        sendError('Quantity must be at least 1', 400);
    }

    // Get product info from cart
    $getStmt = $mysqli->prepare("SELECT p.stock_quantity FROM shopping_cart c 
                                 JOIN products p ON c.product_id = p.product_id 
                                 WHERE c.cart_id = ? AND c.user_id = ?");
    $getStmt->bind_param("ii", $cart_id, $user_id);
    $getStmt->execute();
    $result = $getStmt->get_result();

    if ($result->num_rows === 0) {
        sendError('Cart item not found', 404);
    }

    $product = $result->fetch_assoc();
    $getStmt->close();

    if ($product['stock_quantity'] < $quantity) {
        sendError('Insufficient stock available', 400);
    }

    $stmt = $mysqli->prepare("UPDATE shopping_cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
    $stmt->bind_param("iii", $quantity, $cart_id, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        sendSuccess(null, 'Cart updated', 200);
    } else {
        sendError('Failed to update cart', 500);
    }
    $stmt->close();
}

/**
 * Clear entire cart
 */
function clearCart($mysqli) {
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendError('Invalid request method', 405);
    }

    $user_id = $_SESSION['user_id'];

    $stmt = $mysqli->prepare("DELETE FROM shopping_cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    sendSuccess(null, 'Cart cleared', 200);
    $stmt->close();
}
?>

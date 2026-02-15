<?php
/**
 * Orders API
 * Handles order creation, retrieval, and management
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
        case 'create':
            createOrder($mysqli);
            break;
        case 'list':
            listOrders($mysqli);
            break;
        case 'get':
            getOrder($mysqli);
            break;
        case 'update':
            updateOrder($mysqli);
            break;
        case 'all':
            getAllOrders($mysqli);
            break;
        default:
            sendError('Invalid action', 400);
    }
} catch (Exception $e) {
    error_log('Orders API error: ' . $e->getMessage());
    if (strpos($e->getMessage(), 'password_hash') !== false || strpos($e->getMessage(), 'Unknown column') !== false) {
        sendError('Database schema issue. Please run SETUP_DATABASE.html', 500);
    } else {
        sendError('Server error: ' . $e->getMessage(), 500);
    }
}

/**
 * Create new order from cart
 */
function createOrder($mysqli) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendError('Invalid request method', 405);
    }

    $user_id = $_SESSION['user_id'];
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['delivery_address'])) {
        sendError('Delivery address is required', 400);
    }

    $delivery_address = sanitizeInput($data['delivery_address']);
    $payment_method = sanitizeInput($data['payment_method'] ?? 'Credit Card');
    $notes = sanitizeInput($data['notes'] ?? '');

    // Get cart items
    $cartQuery = "SELECT c.cart_id, c.product_id, c.quantity, p.price, p.stock_quantity
                  FROM shopping_cart c
                  JOIN products p ON c.product_id = p.product_id
                  WHERE c.user_id = ?";

    $stmt = $mysqli->prepare($cartQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cartResult = $stmt->get_result();
    $cartItems = $cartResult->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($cartItems)) {
        sendError('Cart is empty', 400);
    }

    // Start transaction
    $mysqli->begin_transaction();

    try {
        $totalAmount = 0;

        // Calculate total and verify stock
        foreach ($cartItems as $item) {
            if ($item['stock_quantity'] < $item['quantity']) {
                throw new Exception('Insufficient stock for product');
            }
            $totalAmount += $item['price'] * $item['quantity'];
        }

        // Create order
        $orderStmt = $mysqli->prepare("INSERT INTO orders (user_id, total_amount, delivery_address, payment_method, notes, status) 
                                        VALUES (?, ?, ?, ?, ?, 'pending')");
        $orderStmt->bind_param("idsss", $user_id, $totalAmount, $delivery_address, $payment_method, $notes);
        $orderStmt->execute();
        $order_id = $orderStmt->insert_id;
        $orderStmt->close();

        // Add order items and update stock
        foreach ($cartItems as $item) {
            // Insert order item
            $itemStmt = $mysqli->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) 
                                          VALUES (?, ?, ?, ?, ?)");
            $subtotal = $item['price'] * $item['quantity'];
            $itemStmt->bind_param("iiidd", $order_id, $item['product_id'], $item['quantity'], $item['price'], $subtotal);
            $itemStmt->execute();
            $itemStmt->close();

            // Update stock
            $newStock = $item['stock_quantity'] - $item['quantity'];
            $stockStmt = $mysqli->prepare("UPDATE products SET stock_quantity = ? WHERE product_id = ?");
            $stockStmt->bind_param("ii", $newStock, $item['product_id']);
            $stockStmt->execute();
            $stockStmt->close();
        }

        // Clear cart
        $clearStmt = $mysqli->prepare("DELETE FROM shopping_cart WHERE user_id = ?");
        $clearStmt->bind_param("i", $user_id);
        $clearStmt->execute();
        $clearStmt->close();

        // Commit transaction
        $mysqli->commit();

        sendSuccess(['order_id' => $order_id, 'total_amount' => $totalAmount], 'Order created successfully', 201);
    } catch (Exception $e) {
        $mysqli->rollback();
        sendError('Failed to create order: ' . $e->getMessage(), 500);
    }
}

/**
 * List user's orders
 */
function listOrders($mysqli) {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendError('Invalid request method', 405);
    }

    try {
        $user_id = $_SESSION['user_id'];
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        // Get count
        $countResult = $mysqli->query("SELECT COUNT(*) as total FROM orders WHERE user_id = $user_id");
        
        if (!$countResult) {
            error_log('Orders count query failed: ' . $mysqli->error);
            sendError('Failed to retrieve orders count: ' . $mysqli->error, 500);
        }
        
        $countRow = $countResult->fetch_assoc();
        $total = $countRow ? (int)$countRow['total'] : 0;

        // Get orders
        $query = "SELECT o.*, COUNT(oi.order_item_id) as item_count 
                  FROM orders o
                  LEFT JOIN order_items oi ON o.order_id = oi.order_id
                  WHERE o.user_id = $user_id
                  GROUP BY o.order_id
                  ORDER BY o.order_date DESC
                  LIMIT " . (int)$offset . ", " . (int)$limit;

        $result = $mysqli->query($query);
        
        if (!$result) {
            error_log('Orders list query failed: ' . $mysqli->error);
            sendError('Failed to retrieve orders: ' . $mysqli->error, 500);
        }
        
        $orders = $result->fetch_all(MYSQLI_ASSOC);
        
        if (!$orders) {
            $orders = [];
        }
        
        // Convert numeric fields
        foreach ($orders as &$order) {
            $order['order_id'] = (int)$order['order_id'];
            $order['user_id'] = (int)$order['user_id'];
            $order['total_amount'] = (float)$order['total_amount'];
            $order['item_count'] = (int)$order['item_count'];
        }

        sendSuccess([
            'orders' => $orders,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ], 'Orders retrieved', 200);
    } catch (Exception $e) {
        error_log('listOrders exception: ' . $e->getMessage());
        sendError('Error retrieving orders: ' . $e->getMessage(), 500);
    }
}

/**
 * Get order details
 */
function getOrder($mysqli) {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendError('Invalid request method', 405);
    }

    if (!isset($_GET['id'])) {
        sendError('Order ID is required', 400);
    }

    $order_id = (int)$_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Get order
    $stmt = $mysqli->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $orderResult = $stmt->get_result();

    if ($orderResult->num_rows === 0) {
        // Check if admin
        if (!isAdmin()) {
            sendError('Order not found', 404);
        }
        // Admin can view any order
        $adminStmt = $mysqli->prepare("SELECT * FROM orders WHERE order_id = ?");
        $adminStmt->bind_param("i", $order_id);
        $adminStmt->execute();
        $orderResult = $adminStmt->get_result();
        $adminStmt->close();
    }

    if ($orderResult->num_rows === 0) {
        sendError('Order not found', 404);
    }

    $order = $orderResult->fetch_assoc();

    // Get order items
    $itemStmt = $mysqli->prepare("SELECT oi.*, p.product_name, p.image_url 
                                  FROM order_items oi 
                                  JOIN products p ON oi.product_id = p.product_id 
                                  WHERE oi.order_id = ?");
    $itemStmt->bind_param("i", $order_id);
    $itemStmt->execute();
    $itemResult = $itemStmt->get_result();
    $order['items'] = $itemResult->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
    $itemStmt->close();

    sendSuccess($order, 'Order retrieved', 200);
}

/**
 * Get all orders (Admin only)
 */
function getAllOrders($mysqli) {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendError('Invalid request method', 405);
    }

    if (!isAdmin()) {
        sendError('Unauthorized: Admin access required', 403);
    }

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = ITEMS_PER_PAGE;
    $offset = ($page - 1) * $limit;

    // Get count
    $countResult = $mysqli->query("SELECT COUNT(*) as total FROM orders");
    $countRow = $countResult->fetch_assoc();
    $total = $countRow['total'];

    // Get orders
    $query = "SELECT o.*, u.username, u.email, COUNT(oi.order_item_id) as item_count
              FROM orders o
              JOIN users u ON o.user_id = u.user_id
              LEFT JOIN order_items oi ON o.order_id = oi.order_id
              GROUP BY o.order_id
              ORDER BY o.order_date DESC
              LIMIT $offset, $limit";

    $result = $mysqli->query($query);
    $orders = $result->fetch_all(MYSQLI_ASSOC);

    sendSuccess([
        'orders' => $orders,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit)
        ]
    ], 'All orders retrieved', 200);
}

/**
 * Update order status (Admin only)
 */
function updateOrder($mysqli) {
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendError('Invalid request method', 405);
    }

    if (!isAdmin()) {
        sendError('Unauthorized: Admin access required', 403);
    }

    if (!isset($_GET['id'])) {
        sendError('Order ID is required', 400);
    }

    $order_id = (int)$_GET['id'];
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['status'])) {
        sendError('Status is required', 400);
    }

    $status = sanitizeInput($data['status']);
    $validStatuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];

    if (!in_array($status, $validStatuses)) {
        sendError('Invalid status', 400);
    }

    $stmt = $mysqli->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $status, $order_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            sendSuccess(null, 'Order updated successfully', 200);
        } else {
            sendError('Order not found', 404);
        }
    } else {
        sendError('Failed to update order', 500);
    }
    $stmt->close();
}
?>

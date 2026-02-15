<?php
/**
 * Products API
 * Handles product listing, search, filtering, and management
 */

require_once '../config.php';
require_once '../utils.php';

header('Content-Type: application/json');

try {
    $action = isset($_GET['action']) ? $_GET['action'] : '';

    switch ($action) {
        case 'list':
            listProducts($mysqli);
            break;
        case 'get':
            getProduct($mysqli);
            break;
        case 'search':
            searchProducts($mysqli);
            break;
        case 'create':
            createProduct($mysqli);
            break;
        case 'update':
            updateProduct($mysqli);
            break;
        case 'delete':
            deleteProduct($mysqli);
            break;
        case 'by-category':
            getProductsByCategory($mysqli);
            break;
        default:
            sendError('Invalid action', 400);
    }
} catch (Exception $e) {
    error_log('Products API error: ' . $e->getMessage());
    if (strpos($e->getMessage(), 'password_hash') !== false || strpos($e->getMessage(), 'Unknown column') !== false) {
        sendError('Database schema issue. Please run SETUP_DATABASE.html', 500);
    } else {
        sendError('Server error: ' . $e->getMessage(), 500);
    }
}

/**
 * List all products with pagination
 */
function listProducts($mysqli) {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendError('Invalid request method', 405);
    }

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = ITEMS_PER_PAGE;
    $offset = ($page - 1) * $limit;

    try {
        // Get total count
        $countResult = $mysqli->query("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
        
        if (!$countResult) {
            error_log('Product count query failed: ' . $mysqli->error);
            sendError('Failed to retrieve product count: ' . $mysqli->error, 500);
        }
        
        $countRow = $countResult->fetch_assoc();
        $total = $countRow ? (int)$countRow['total'] : 0;

        // Get products
        $query = "SELECT p.*, c.category_name FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.category_id 
                  WHERE p.is_active = 1 
                  ORDER BY p.created_at DESC 
                  LIMIT " . (int)$offset . ", " . (int)$limit;

        $result = $mysqli->query($query);
        
        if (!$result) {
            error_log('Product list query failed: ' . $mysqli->error);
            sendError('Failed to retrieve products: ' . $mysqli->error, 500);
        }
        
        $products = $result->fetch_all(MYSQLI_ASSOC);
        
        if (!$products) {
            $products = [];
        }

        sendSuccess([
            'products' => $products,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ], 'Products retrieved', 200);
    } catch (Exception $e) {
        error_log('listProducts exception: ' . $e->getMessage());
        sendError('Error retrieving products: ' . $e->getMessage(), 500);
    }
}

/**
 * Get single product by ID
 */
function getProduct($mysqli) {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendError('Invalid request method', 405);
    }

    if (!isset($_GET['id'])) {
        sendError('Product ID is required', 400);
    }

    $product_id = (int)$_GET['id'];

    $stmt = $mysqli->prepare("SELECT p.*, c.category_name FROM products p 
                              LEFT JOIN categories c ON p.category_id = c.category_id 
                              WHERE p.product_id = ? AND p.is_active = 1");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        sendError('Product not found', 404);
    }

    $product = $result->fetch_assoc();

    // Get reviews
    $reviewStmt = $mysqli->prepare("SELECT r.*, u.first_name, u.last_name FROM reviews r 
                                    LEFT JOIN users u ON r.user_id = u.user_id 
                                    WHERE r.product_id = ? 
                                    ORDER BY r.created_at DESC");
    $reviewStmt->bind_param("i", $product_id);
    $reviewStmt->execute();
    $reviewResult = $reviewStmt->get_result();
    $product['reviews'] = $reviewResult->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
    $reviewStmt->close();

    sendSuccess($product, 'Product retrieved', 200);
}

/**
 * Search products
 */
function searchProducts($mysqli) {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendError('Invalid request method', 405);
    }

    try {
        $searchTerm = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';
        $category = isset($_GET['category']) ? (int)$_GET['category'] : null;
        $minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
        $maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        // Build query
        $query = "SELECT p.*, c.category_name FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.category_id 
                  WHERE p.is_active = 1";

        if (!empty($searchTerm)) {
            $searchTerm = "%{$searchTerm}%";
            $query .= " AND (p.product_name LIKE '{$mysqli->real_escape_string($searchTerm)}' 
                            OR p.description LIKE '{$mysqli->real_escape_string($searchTerm)}')";
        }

        if ($category !== null) {
            $query .= " AND p.category_id = {$category}";
        }

        if ($minPrice !== null) {
            $query .= " AND p.price >= {$minPrice}";
        }

        if ($maxPrice !== null) {
            $query .= " AND p.price <= {$maxPrice}";
        }

        // Get count
        $countResult = $mysqli->query("SELECT COUNT(*) as total FROM ({$query}) as counted");
        
        if (!$countResult) {
            error_log('Search count query failed: ' . $mysqli->error);
            sendError('Failed to retrieve search count: ' . $mysqli->error, 500);
        }
        
        $countRow = $countResult->fetch_assoc();
        $total = $countRow ? (int)$countRow['total'] : 0;

        // Get products
        $query .= " ORDER BY p.created_at DESC LIMIT " . (int)$offset . ", " . (int)$limit;
        $result = $mysqli->query($query);
        
        if (!$result) {
            error_log('Search products query failed: ' . $mysqli->error);
            sendError('Failed to retrieve search results: ' . $mysqli->error, 500);
        }
        
        $products = $result->fetch_all(MYSQLI_ASSOC);
        
        if (!$products) {
            $products = [];
        }

        sendSuccess([
            'products' => $products,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ], 'Products searched', 200);
    } catch (Exception $e) {
        error_log('searchProducts exception: ' . $e->getMessage());
        sendError('Error searching products: ' . $e->getMessage(), 500);
    }
}

/**
 * Get products by category
 */
function getProductsByCategory($mysqli) {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendError('Invalid request method', 405);
    }

    if (!isset($_GET['id'])) {
        sendError('Category ID is required', 400);
    }

    try {
        $category_id = (int)$_GET['id'];
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        // Verify category exists
        $catStmt = $mysqli->prepare("SELECT category_id FROM categories WHERE category_id = ?");
        
        if (!$catStmt) {
            error_log('Category prepare failed: ' . $mysqli->error);
            sendError('Failed to verify category: ' . $mysqli->error, 500);
        }
        
        $catStmt->bind_param("i", $category_id);
        $catStmt->execute();
        
        if ($catStmt->get_result()->num_rows === 0) {
            sendError('Category not found', 404);
        }
        $catStmt->close();

        // Get count
        $countResult = $mysqli->query("SELECT COUNT(*) as total FROM products WHERE category_id = $category_id AND is_active = 1");
        
        if (!$countResult) {
            error_log('Category count query failed: ' . $mysqli->error);
            sendError('Failed to retrieve category product count: ' . $mysqli->error, 500);
        }
        
        $countRow = $countResult->fetch_assoc();
        $total = $countRow ? (int)$countRow['total'] : 0;

        // Get products
        $query = "SELECT p.*, c.category_name FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.category_id 
                  WHERE p.category_id = $category_id AND p.is_active = 1 
                  ORDER BY p.created_at DESC 
                  LIMIT " . (int)$offset . ", " . (int)$limit;

        $result = $mysqli->query($query);
        
        if (!$result) {
            error_log('Category products query failed: ' . $mysqli->error);
            sendError('Failed to retrieve category products: ' . $mysqli->error, 500);
        }
        
        $products = $result->fetch_all(MYSQLI_ASSOC);
        
        if (!$products) {
            $products = [];
        }

        sendSuccess([
            'products' => $products,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ], 'Category products retrieved', 200);
    } catch (Exception $e) {
        error_log('getProductsByCategory exception: ' . $e->getMessage());
        sendError('Error retrieving category products: ' . $e->getMessage(), 500);
    }
}

/**
 * Create new product (Admin only)
 */
function createProduct($mysqli) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendError('Invalid request method', 405);
    }

    if (!isAdmin()) {
        sendError('Unauthorized: Admin access required', 403);
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['product_name']) || empty($data['category_id']) || empty($data['price']) || empty($data['sku'])) {
        sendError('Required fields: product_name, category_id, price, sku', 400);
    }

    $product_name = sanitizeInput($data['product_name']);
    $category_id = (int)$data['category_id'];
    $description = sanitizeInput($data['description'] ?? '');
    $price = (float)$data['price'];
    $stock_quantity = (int)($data['stock_quantity'] ?? 0);
    $sku = sanitizeInput($data['sku']);
    $image_url = sanitizeInput($data['image_url'] ?? '');

    $stmt = $mysqli->prepare("INSERT INTO products (category_id, product_name, description, price, stock_quantity, sku, image_url) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        sendError('Failed to prepare product insert: ' . $mysqli->error, 500);
    }

    $types = 'issdisss';
    $values = [$category_id, $product_name, $description, $price, $stock_quantity, $sku, $image_url];
    if (!bindParams($stmt, $types, $values)) {
        sendError('Failed to bind parameters for product insert', 500);
    }

    if ($stmt->execute()) {
        $product_id = $stmt->insert_id;
        sendSuccess(['product_id' => $product_id], 'Product created successfully', 201);
    } else {
        sendError('Failed to create product: ' . $stmt->error, 500);
    }
    $stmt->close();
}

/**
 * Update product (Admin only)
 */
function updateProduct($mysqli) {
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendError('Invalid request method', 405);
    }

    if (!isAdmin()) {
        sendError('Unauthorized: Admin access required', 403);
    }

    if (!isset($_GET['id'])) {
        sendError('Product ID is required', 400);
    }

    $product_id = (int)$_GET['id'];
    $data = json_decode(file_get_contents('php://input'), true);

    // Check if product exists
    $stmt = $mysqli->prepare("SELECT product_id FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        sendError('Product not found', 404);
    }
    $stmt->close();

    // Update only provided fields
    $updates = [];
    $types = '';
    $values = [];

    if (isset($data['product_name'])) {
        $updates[] = 'product_name = ?';
        $values[] = sanitizeInput($data['product_name']);
        $types .= 's';
    }
    if (isset($data['description'])) {
        $updates[] = 'description = ?';
        $values[] = sanitizeInput($data['description']);
        $types .= 's';
    }
    if (isset($data['price'])) {
        $updates[] = 'price = ?';
        $values[] = (float)$data['price'];
        $types .= 'd';
    }
    if (isset($data['stock_quantity'])) {
        $updates[] = 'stock_quantity = ?';
        $values[] = (int)$data['stock_quantity'];
        $types .= 'i';
    }
    if (isset($data['image_url'])) {
        $updates[] = 'image_url = ?';
        $values[] = sanitizeInput($data['image_url']);
        $types .= 's';
    }
    if (isset($data['is_active'])) {
        $updates[] = 'is_active = ?';
        $values[] = (bool)$data['is_active'] ? 1 : 0;
        $types .= 'i';
    }

    if (empty($updates)) {
        sendError('No fields to update', 400);
    }

    $values[] = $product_id;
    $types .= 'i';

    $query = "UPDATE products SET " . implode(', ', $updates) . " WHERE product_id = ?";
    $stmt = $mysqli->prepare($query);
    if (!bindParams($stmt, $types, $values)) {
        sendError('Failed to bind parameters', 500);
    }

    if ($stmt->execute()) {
        sendSuccess(null, 'Product updated successfully', 200);
    } else {
        sendError('Failed to update product: ' . $stmt->error, 500);
    }
    $stmt->close();
}

/**
 * Delete product (Admin only)
 */
function deleteProduct($mysqli) {
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendError('Invalid request method', 405);
    }

    if (!isAdmin()) {
        sendError('Unauthorized: Admin access required', 403);
    }

    if (!isset($_GET['id'])) {
        sendError('Product ID is required', 400);
    }

    $product_id = (int)$_GET['id'];

    // Soft delete (mark as inactive)
    $stmt = $mysqli->prepare("UPDATE products SET is_active = 0 WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            sendSuccess(null, 'Product deleted successfully', 200);
        } else {
            sendError('Product not found', 404);
        }
    } else {
        sendError('Failed to delete product: ' . $stmt->error, 500);
    }
    $stmt->close();
}
?>

<?php
/**
 * Categories API
 * Manages product categories
 */

require_once '../config.php';
require_once '../utils.php';

header('Content-Type: application/json');

try {
    $action = isset($_GET['action']) ? $_GET['action'] : '';

    switch ($action) {
        case 'list':
            listCategories($mysqli);
            break;
        case 'get':
            getCategory($mysqli);
            break;
        case 'create':
            createCategory($mysqli);
            break;
        case 'update':
            updateCategory($mysqli);
            break;
        case 'delete':
            deleteCategory($mysqli);
            break;
        default:
            sendError('Invalid action', 400);
    }
} catch (Exception $e) {
    error_log('Categories API error: ' . $e->getMessage());
    if (strpos($e->getMessage(), 'password_hash') !== false || strpos($e->getMessage(), 'Unknown column') !== false) {
        sendError('Database schema issue. Please run SETUP_DATABASE.html', 500);
    } else {
        sendError('Server error: ' . $e->getMessage(), 500);
    }
}

/**
 * List all categories
 */
function listCategories($mysqli) {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendError('Invalid request method', 405);
    }

    try {
        $query = "SELECT c.*, COUNT(p.product_id) as product_count 
                  FROM categories c
                  LEFT JOIN products p ON c.category_id = p.category_id AND p.is_active = 1
                  GROUP BY c.category_id
                  ORDER BY c.category_name ASC";

        $result = $mysqli->query($query);
        
        if (!$result) {
            error_log('Categories list query failed: ' . $mysqli->error);
            sendError('Failed to retrieve categories: ' . $mysqli->error, 500);
        }
        
        $categories = $result->fetch_all(MYSQLI_ASSOC);
        
        if (!$categories) {
            $categories = [];
        }

        sendSuccess($categories, 'Categories retrieved', 200);
    } catch (Exception $e) {
        error_log('listCategories exception: ' . $e->getMessage());
        sendError('Error retrieving categories: ' . $e->getMessage(), 500);
    }
}

/**
 * Get single category
 */
function getCategory($mysqli) {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendError('Invalid request method', 405);
    }

    if (!isset($_GET['id'])) {
        sendError('Category ID is required', 400);
    }

    $category_id = (int)$_GET['id'];

    $stmt = $mysqli->prepare("SELECT c.*, COUNT(p.product_id) as product_count 
                              FROM categories c
                              LEFT JOIN products p ON c.category_id = p.category_id AND p.is_active = 1
                              WHERE c.category_id = ?
                              GROUP BY c.category_id");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        sendError('Category not found', 404);
    }

    $category = $result->fetch_assoc();
    $stmt->close();

    sendSuccess($category, 'Category retrieved', 200);
}

/**
 * Create category (Admin only)
 */
function createCategory($mysqli) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendError('Invalid request method', 405);
    }

    if (!isAdmin()) {
        sendError('Unauthorized: Admin access required', 403);
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['category_name'])) {
        sendError('Category name is required', 400);
    }

    $category_name = sanitizeInput($data['category_name']);
    $description = sanitizeInput($data['description'] ?? '');
    $image_url = sanitizeInput($data['image_url'] ?? '');

    $stmt = $mysqli->prepare("INSERT INTO categories (category_name, description, image_url) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $category_name, $description, $image_url);

    if ($stmt->execute()) {
        $category_id = $stmt->insert_id;
        sendSuccess(['category_id' => $category_id], 'Category created successfully', 201);
    } else {
        sendError('Failed to create category: ' . $stmt->error, 500);
    }
    $stmt->close();
}

/**
 * Update category (Admin only)
 */
function updateCategory($mysqli) {
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendError('Invalid request method', 405);
    }

    if (!isAdmin()) {
        sendError('Unauthorized: Admin access required', 403);
    }

    if (!isset($_GET['id'])) {
        sendError('Category ID is required', 400);
    }

    $category_id = (int)$_GET['id'];
    $data = json_decode(file_get_contents('php://input'), true);

    // Check if category exists
    $stmt = $mysqli->prepare("SELECT category_id FROM categories WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        sendError('Category not found', 404);
    }
    $stmt->close();

    $updates = [];
    $types = '';
    $values = [];

    if (isset($data['category_name'])) {
        $updates[] = 'category_name = ?';
        $values[] = sanitizeInput($data['category_name']);
        $types .= 's';
    }
    if (isset($data['description'])) {
        $updates[] = 'description = ?';
        $values[] = sanitizeInput($data['description']);
        $types .= 's';
    }
    if (isset($data['image_url'])) {
        $updates[] = 'image_url = ?';
        $values[] = sanitizeInput($data['image_url']);
        $types .= 's';
    }

    if (empty($updates)) {
        sendError('No fields to update', 400);
    }

    $values[] = $category_id;
    $types .= 'i';

    $query = "UPDATE categories SET " . implode(', ', $updates) . " WHERE category_id = ?";
    $stmt = $mysqli->prepare($query);
    if (!bindParams($stmt, $types, $values)) {
        sendError('Failed to bind parameters', 500);
    }

    if ($stmt->execute()) {
        sendSuccess(null, 'Category updated successfully', 200);
    } else {
        sendError('Failed to update category', 500);
    }
    $stmt->close();
}

/**
 * Delete category (Admin only)
 */
function deleteCategory($mysqli) {
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendError('Invalid request method', 405);
    }

    if (!isAdmin()) {
        sendError('Unauthorized: Admin access required', 403);
    }

    if (!isset($_GET['id'])) {
        sendError('Category ID is required', 400);
    }

    $category_id = (int)$_GET['id'];

    // Check if category has products
    $stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ? AND is_active = 1");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row['count'] > 0) {
        sendError('Cannot delete category with products', 409);
    }

    // Delete category
    $deleteStmt = $mysqli->prepare("DELETE FROM categories WHERE category_id = ?");
    $deleteStmt->bind_param("i", $category_id);

    if ($deleteStmt->execute()) {
        if ($deleteStmt->affected_rows > 0) {
            sendSuccess(null, 'Category deleted successfully', 200);
        } else {
            sendError('Category not found', 404);
        }
    } else {
        sendError('Failed to delete category', 500);
    }
    $deleteStmt->close();
}
?>

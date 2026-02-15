<?php
/**
 * Database Schema Fixer
 * Adds missing columns and fixes table structure
 */

header('Content-Type: application/json');

require_once 'config.php';

$response = [
    'success' => false,
    'changes' => [],
    'errors' => []
];

try {
    // Check if password_hash column exists
    $check = $mysqli->query("SHOW COLUMNS FROM users LIKE 'password_hash'");
    
    if ($check && $check->num_rows === 0) {
        // Column doesn't exist, add it
        $response['changes'][] = 'Adding missing password_hash column to users table';
        
        if ($mysqli->query("ALTER TABLE users ADD COLUMN password_hash VARCHAR(255) NOT NULL AFTER email")) {
            $response['changes'][] = 'Successfully added password_hash column';
        } else {
            throw new Exception("Failed to add password_hash column: " . $mysqli->error);
        }
    } else {
        $response['changes'][] = 'password_hash column already exists';
    }

    // Check other required columns
    $required_columns = [
        'users' => ['user_id', 'username', 'email', 'password_hash', 'first_name', 'last_name', 'role', 'is_active'],
        'categories' => ['category_id', 'category_name'],
        'products' => ['product_id', 'category_id', 'product_name', 'price', 'stock_quantity'],
    ];

    foreach ($required_columns as $table => $columns) {
        foreach ($columns as $column) {
            $check = $mysqli->query("SHOW COLUMNS FROM {$table} LIKE '{$column}'");
            if (!$check || $check->num_rows === 0) {
                $response['errors'][] = "Missing column '{$column}' in table '{$table}'";
            }
        }
    }

    $response['success'] = count($response['errors']) === 0;

    // Try to get sample data count
    $res = $mysqli->query("SELECT COUNT(*) as count FROM users");
    if (!$res) {
        throw new Exception('Failed to query users table: ' . $mysqli->error);
    }
    $users = $res->fetch_assoc();

    $res = $mysqli->query("SELECT COUNT(*) as count FROM categories");
    if (!$res) {
        throw new Exception('Failed to query categories table: ' . $mysqli->error);
    }
    $categories = $res->fetch_assoc();

    $res = $mysqli->query("SELECT COUNT(*) as count FROM products");
    if (!$res) {
        throw new Exception('Failed to query products table: ' . $mysqli->error);
    }
    $products = $res->fetch_assoc();

    $response['data_status'] = [
        'users' => (int)$users['count'],
        'categories' => (int)$categories['count'],
        'products' => (int)$products['count']
    ];

    // If no data, provide sample data insert instructions
    if ((int)$users['count'] === 0) {
        $response['warning'] = 'No sample data found. You may need to import sample data.';
        $response['next_step'] = 'Use backend/insert-sample-data.php to add sample data';
    }

} catch (Exception $e) {
    $response['errors'][] = $e->getMessage();
    $response['success'] = false;
}

echo json_encode($response, JSON_PRETTY_PRINT);
$mysqli->close();
?>

<?php
/**
 * Database Connection Test
 * Use this to diagnose MySQL and database issues
 */

header('Content-Type: application/json');

// First test: Check if config file exists
if (!file_exists('config.php')) {
    die(json_encode(['success' => false, 'error' => 'config.php not found']));
}

require_once 'config.php';

$response = [
    'mysql_running' => false,
    'database_exists' => false,
    'tables_exist' => false,
    'credentials' => [
        'host' => DB_HOST,
        'user' => DB_USER,
        'database' => DB_NAME
    ],
    'errors' => [],
    'warnings' => []
];

// Test 1: Check MySQL connection
$test_mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS);

if ($test_mysqli->connect_error) {
    $response['errors'][] = "MySQL Connection Failed: " . $test_mysqli->connect_error;
    $response['errors'][] = "Check that:";
    $response['errors'][] = "1. MySQL is running";
    $response['errors'][] = "2. Database password in config.php is correct";
    $response['errors'][] = "3. Host is localhost";
    echo json_encode($response);
    exit;
}

$response['mysql_running'] = true;

// Test 2: Check if database exists
$databases = $test_mysqli->query("SHOW DATABASES LIKE '" . DB_NAME . "'");

if ($databases && $databases->num_rows > 0) {
    $response['database_exists'] = true;
} else {
    $response['errors'][] = "Database '" . DB_NAME . "' does not exist";
    $response['errors'][] = "Solution: Import the SQL file using phpMyAdmin or MySQL command line";
    echo json_encode($response);
    exit;
}

// Test 3: Check if tables exist (using proper connection to database)
$mysqli->select_db(DB_NAME);

$tables = ['users', 'categories', 'products', 'cart_items', 'orders', 'order_items', 'reviews'];
$tables_status = [];

foreach ($tables as $table) {
    $result = $mysqli->query("SHOW TABLES LIKE '" . $table . "'");
    if ($result && $result->num_rows > 0) {
        $tables_status[$table] = 'OK';
    } else {
        $tables_status[$table] = 'MISSING';
    }
}

$all_tables_exist = !in_array('MISSING', $tables_status);

if ($all_tables_exist) {
    $response['tables_exist'] = true;
} else {
    $response['errors'][] = "Some database tables are missing";
    $response['warnings'][] = "Missing tables: " . implode(', ', array_keys(array_filter($tables_status, function($v) { return $v === 'MISSING'; })));
    $response['errors'][] = "Solution: Re-import the SQL file";
}

$response['tables'] = $tables_status;

// Test 4: Check sample data
if ($all_tables_exist) {
    $res = $mysqli->query("SELECT COUNT(*) as count FROM users");
    if (!$res) { $response['errors'][] = 'Failed to query users: ' . $mysqli->error; }
    $user_count = (int)($res ? $res->fetch_assoc()['count'] : 0);

    $res = $mysqli->query("SELECT COUNT(*) as count FROM categories");
    if (!$res) { $response['errors'][] = 'Failed to query categories: ' . $mysqli->error; }
    $category_count = (int)($res ? $res->fetch_assoc()['count'] : 0);

    $res = $mysqli->query("SELECT COUNT(*) as count FROM products");
    if (!$res) { $response['errors'][] = 'Failed to query products: ' . $mysqli->error; }
    $product_count = (int)($res ? $res->fetch_assoc()['count'] : 0);
    
    $response['data_summary'] = [
        'users' => $user_count,
        'categories' => $category_count,
        'products' => $product_count
    ];
    
    if ($user_count === 0 || $category_count === 0 || $product_count === 0) {
        $response['warnings'][] = "Some tables are empty. You may need to import sample data.";
    }
}

// Test 5: Try a simple query
if ($all_tables_exist) {
    $test_query = $mysqli->query("SELECT * FROM categories LIMIT 1");
    if ($test_query) {
        $response['query_test'] = 'SUCCESS';
    } else {
        $response['errors'][] = "Query execution failed: " . $mysqli->error;
    }
}

$response['success'] = empty($response['errors']);

echo json_encode($response, JSON_PRETTY_PRINT);

$test_mysqli->close();
$mysqli->close();
?>

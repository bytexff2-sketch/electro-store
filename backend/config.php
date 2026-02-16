<?php
/**
 * Database Configuration File
 * Stores database connection parameters
 */

// Disable HTML error display, use exceptions for JSON APIs
error_reporting(E_ALL);
ini_set('display_errors', 0);  // Don't show HTML errors
ini_set('log_errors', 1);       // Log errors to file

// Database credentials - use environment variables
define('DB_HOST', 'localhost');
define('DB_USER', 'a264133admin');
define('DB_PASS', 'Andrea2004@');
define('DB_NAME', 'a264133_pt5q8913');
define('DB_PORT', '3306');

// Application settings
define('APP_NAME', 'Electronics Store');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

// Security settings
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_OPTIONS', ['cost' => 10]);

// Pagination
define('ITEMS_PER_PAGE', 12);

// Create MySQLi connection
// Use exceptions for database errors instead of warnings
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    // Set charset to UTF-8
    $mysqli->set_charset("utf8mb4");
} catch (Exception $e) {
    header('Content-Type: application/json');
    // Do not expose internal error details in production
    $msg = 'Database connection failed: ' . $e->getMessage();
    error_log($msg);
    die(json_encode(['success' => false, 'message' => 'Database connection failed. Check server logs.']));
}

// End of config

<?php
/**
 * Security and Utility Functions
 * Handles password hashing, input sanitization, CSRF protection
 */

// Initialize session. If a client provides a session id via the X-Session-Id header
// use that session id so API requests can be authenticated even if cookies are not preserved.
if (php_sapi_name() !== 'cli') {
    // If no cookie was sent but a header is present, set session id before starting session
    $hdr = null;
    if (isset($_SERVER['HTTP_X_SESSION_ID']) && !empty($_SERVER['HTTP_X_SESSION_ID'])) {
        $hdr = $_SERVER['HTTP_X_SESSION_ID'];
    }

    if (empty($_COOKIE[session_name()]) && $hdr) {
        session_id($hdr);
    }

    session_start();
}

/**
 * Hash a password using bcrypt
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

/**
 * Verify a password against its hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Sanitize user input to prevent XSS attacks
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate password strength
 * Requires: min 8 chars, uppercase, lowercase, number
 */
function validatePassword($password) {
    if (strlen($password) < 8) {
        return false;
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    return true;
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'role' => $_SESSION['role'] ?? null,
        'first_name' => $_SESSION['first_name'] ?? null,
        'last_name' => $_SESSION['last_name'] ?? null
    ];
}

/**
 * Verify user exists in database
 */
function userExists($mysqli, $user_id) {
    try {
        $stmt = $mysqli->prepare("SELECT user_id FROM users WHERE user_id = ? LIMIT 1");
        if (!$stmt) {
            error_log('userExists prepare failed: ' . $mysqli->error);
            return false;
        }
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    } catch (Exception $e) {
        error_log('userExists error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Escape string for database queries
 */
function escapeString($mysqli, $string) {
    return $mysqli->real_escape_string($string);
}

/**
 * Bind parameters to a mysqli_stmt dynamically using an array of values.
 * This handles references required by bind_param.
 * Returns true on success, false otherwise.
 */
function bindParams($stmt, $types, $values) {
    if (empty($types) || empty($values)) {
        return false;
    }

    $bind_names = [];
    $bind_names[] = $types;
    foreach ($values as $key => $val) {
        $bind_names[] = &$values[$key];
    }

    return call_user_func_array([$stmt, 'bind_param'], $bind_names);
}

/**
 * Redirect to another page
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Return JSON response
 */
function jsonResponse($success, $message, $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    $response = [
        'success' => $success,
        'message' => $message
    ];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit();
}

/**
 * Validate file upload
 */
function validateFileUpload($file, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'], $maxSize = 5242880) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error'];
    }

    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File too large'];
    }

    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }

    return ['success' => true];
}

/**
 * Generate random token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Paginate array
 */
function paginate($array, $page = 1, $perPage = 12) {
    $maxPages = ceil(count($array) / $perPage);
    if ($page > $maxPages || $page < 1) {
        $page = 1;
    }
    
    $start = ($page - 1) * $perPage;
    return [
        'data' => array_slice($array, $start, $perPage),
        'current_page' => $page,
        'total_pages' => $maxPages,
        'total_items' => count($array)
    ];
}

/**
 * Send JSON error response
 */
function sendError($message, $statusCode = 400) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $message]);
    exit();
}

/**
 * Send JSON success response
 */
function sendSuccess($data, $message = 'Success', $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => $message, 'data' => $data]);
    exit();
}
?>

<?php
/**
 * User Authentication API
 * Handles login, register, and logout
 */

require_once '../config.php';
require_once '../utils.php';

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'register':
        handleRegister($mysqli);
        break;
    case 'login':
        handleLogin($mysqli);
        break;
    case 'logout':
        handleLogout();
        break;
    case 'check':
        handleCheckSession($mysqli);
        break;
    default:
        sendError('Invalid action', 400);
}

/**
 * Handle user registration
 */
function handleRegister($mysqli) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendError('Invalid request method', 405);
    }

    try {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validate input
        if (empty($data['username']) || empty($data['email']) || empty($data['password']) || empty($data['first_name']) || empty($data['last_name'])) {
            sendError('All fields are required', 400);
            return;
        }

        $username = sanitizeInput($data['username']);
        $email = sanitizeInput($data['email']);
        $password = $data['password'];
        $first_name = sanitizeInput($data['first_name']);
        $last_name = sanitizeInput($data['last_name']);

        // Validate email
        if (!validateEmail($email)) {
            sendError('Invalid email format', 400);
            return;
        }

        // Check if username already exists
        $stmt = $mysqli->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        if (!$stmt) {
            sendError('Database error: ' . $mysqli->error, 500);
            return;
        }
        
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            sendError('Username or email already exists', 409);
            $stmt->close();
            return;
        }
        $stmt->close();

        // Hash password
        $password_hash = hashPassword($password);

        // Insert new user
        $stmt = $mysqli->prepare("INSERT INTO users (username, email, password_hash, first_name, last_name, role) VALUES (?, ?, ?, ?, ?, 'customer')");
        if (!$stmt) {
            sendError('Database error: ' . $mysqli->error, 500);
            return;
        }
        
        $stmt->bind_param("sssss", $username, $email, $password_hash, $first_name, $last_name);
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            sendSuccess(['user_id' => $user_id, 'username' => $username], 'Registration successful', 201);
        } else {
            sendError('Registration failed: ' . $mysqli->error, 500);
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log('Registration error: ' . $e->getMessage());
        
        if (strpos($e->getMessage(), 'password_hash') !== false) {
            sendError('System error: Database schema issue. Please run SETUP_DATABASE.html', 500);
        } else {
            sendError('Registration failed: ' . $e->getMessage(), 500);
        }
    }
}

/**
 * Handle user login
 */
function handleLogin($mysqli) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendError('Invalid request method', 405);
    }

    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['username']) || empty($data['password'])) {
            sendError('Username and password are required', 400);
            return;
        }

        $username = sanitizeInput($data['username']);
        $password = $data['password'];

        // Get user from database
        $stmt = $mysqli->prepare("SELECT user_id, password_hash, email, role, first_name, last_name FROM users WHERE username = ? AND is_active = 1");
        if (!$stmt) {
            sendError('Database error: ' . $mysqli->error, 500);
            return;
        }
        
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            sendError('Invalid username or password', 401);
            $stmt->close();
            return;
        }

        $user = $result->fetch_assoc();
        $stmt->close();

        // Verify password
        if (!verifyPassword($password, $user['password_hash'])) {
            sendError('Invalid username or password', 401);
            return;
        }

        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['login_time'] = time();

        $userData = [
            'user_id' => $user['user_id'],
            'username' => $username,
            'email' => $user['email'],
            'role' => $user['role'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'session_id' => session_id()
        ];

        sendSuccess($userData, 'Login successful', 200);
    } catch (Exception $e) {
        // Catch any database or other exceptions
        error_log('Login error: ' . $e->getMessage());
        
        // Check if it's a missing column error
        if (strpos($e->getMessage(), 'password_hash') !== false) {
            sendError('System error: Database schema issue. Please run SETUP_DATABASE.html', 500);
        } else {
            sendError('Login failed: ' . $e->getMessage(), 500);
        }
    }
}

/**
 * Handle user logout
 */
function handleLogout() {
    session_destroy();
    sendSuccess(null, 'Logout successful', 200);
}

/**
 * Check if user session is valid
 */
function handleCheckSession($mysqli) {
    if (isLoggedIn()) {
        $user_id = $_SESSION['user_id'];
        
        // Verify user exists in database
        if (!userExists($mysqli, $user_id)) {
            error_log('Session contains invalid user_id ' . $user_id);
            session_destroy();
            sendError('Session expired. Please login again.', 401);
            return;
        }
        
        $user = getCurrentUser();
        sendSuccess($user, 'Session valid', 200);
    } else {
        sendError('Not logged in', 401);
    }
}
?>

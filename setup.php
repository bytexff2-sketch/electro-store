<?php
/**
 * Automated Setup Script for Electronics Store
 * Run this once after deployment to initialize the database
 * Access via: https://your-domain.com/setup.php or https://your-domain.com/admin/setup.php
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$setup_complete = false;
$error_message = '';
$success_message = '';

// Get database configuration from environment variables
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';
$db_port = getenv('DB_PORT') ?: '3306';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup_database'])) {
    try {
        // Connect to MySQL without selecting a database (to create database)
        $mysqli = new mysqli($db_host, $db_user, $db_pass, '', (int)$db_port);
        
        if ($mysqli->connect_error) {
            throw new Exception("Connection failed: " . $mysqli->connect_error);
        }

        // Read the SQL schema file
        $sql_file = __DIR__ . '/backend/database/electronics_store.sql';
        
        if (!file_exists($sql_file)) {
            throw new Exception("SQL file not found: " . $sql_file);
        }

        $sql_content = file_get_contents($sql_file);
        
        // Split SQL statements (simple approach - works for most cases)
        $statements = array_filter(
            preg_split('/;[\s\n]+/', $sql_content),
            function($stmt) { return trim($stmt) !== ''; }
        );

        // Execute each statement
        foreach ($statements as $statement) {
            $stmt = trim($statement);
            if (!empty($stmt)) {
                if (!$mysqli->query($stmt)) {
                    throw new Exception("SQL Error: " . $mysqli->error . "\nStatement: " . substr($stmt, 0, 100));
                }
            }
        }

        // Verify database was created
        $check_result = $mysqli->query("SELECT DATABASE()");
        $row = $check_result->fetch_row();
        
        $success_message = "✅ Database initialization successful!<br>";
        $success_message .= "✅ Database 'electronics_store' created<br>";
        $success_message .= "✅ All tables created successfully<br>";
        $success_message .= "✅ Schema imported successfully<br><br>";
        $success_message .= "<strong>Your site is ready to use!</strong>";
        
        $setup_complete = true;
        $mysqli->close();

    } catch (Exception $e) {
        $error_message = "❌ Setup failed: " . $e->getMessage();
    }
}

// Check database connection status
$connection_status = 'Not Checked';
$connection_color = 'gray';

try {
    $test_mysqli = new mysqli($db_host, $db_user, $db_pass, '', (int)$db_port);
    if (!$test_mysqli->connect_error) {
        $connection_status = '✅ Connected';
        $connection_color = 'green';
        $test_mysqli->close();
    }
} catch (Exception $e) {
    $connection_status = '❌ Failed: ' . $e->getMessage();
    $connection_color = 'red';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electronics Store - Setup</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .status-section {
            background: #f5f5f5;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            border-left: 4px solid #667eea;
        }
        
        .status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .status-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        
        .status-label {
            font-weight: 600;
            color: #333;
        }
        
        .status-value {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        
        .status-value.green {
            background: #d4edda;
            color: #155724;
        }
        
        .status-value.red {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-value.gray {
            background: #e2e3e5;
            color: #383d41;
        }
        
        .message {
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            font-size: 14px;
            line-height: 1.8;
        }
        
        .message.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .message.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .form-section {
            margin-top: 30px;
            text-align: center;
        }
        
        .credentials {
            background: #f0f0f0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            font-size: 13px;
            text-align: left;
        }
        
        .credentials p {
            margin-bottom: 8px;
            font-family: 'Courier New', monospace;
        }
        
        .credentials strong {
            display: block;
            margin-bottom: 10px;
            color: #333;
        }
        
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .setup-complete {
            text-align: center;
        }
        
        .setup-complete h2 {
            color: #155724;
            margin-bottom: 20px;
        }
        
        .success-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        
        .next-steps {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
            text-align: left;
        }
        
        .next-steps h3 {
            color: #2e7d32;
            margin-bottom: 10px;
        }
        
        .next-steps ol {
            margin-left: 20px;
        }
        
        .next-steps li {
            margin-bottom: 8px;
            color: #333;
        }
        
        .config-error {
            background: #fff9c4;
            border-left: 4px solid #fbc02d;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📱 Electronics Store</h1>
        <p class="subtitle">Database Setup & Initialization</p>
        
        <?php if ($setup_complete): ?>
            <div class="message success">
                <div class="setup-complete">
                    <div class="success-icon">✅</div>
                    <h2>Setup Complete!</h2>
                    <?php echo $success_message; ?>
                </div>
                <div class="next-steps">
                    <h3>🎉 Next Steps:</h3>
                    <ol>
                        <li>Visit <strong><?php echo getenv('APP_URL') ?: 'your website'; ?></strong> to see your live site</li>
                        <li>Login with test account (if created) or register a new account</li>
                        <li>Browse products and test the store functionality</li>
                        <li><strong>⚠️ IMPORTANT:</strong> Delete this setup.php file after setup is complete for security</li>
                    </ol>
                </div>
            </div>
        <?php elseif ($error_message): ?>
            <div class="message error">
                <strong>❌ Error During Setup</strong><br>
                <?php echo htmlspecialchars($error_message); ?>
                <br><br>
                <strong>Troubleshooting:</strong><br>
                1. Verify your database credentials are correct<br>
                2. Check that your database user has CREATE DATABASE privileges<br>
                3. Ensure MySQL is running on the server<br>
                4. Try again below after fixing the issue
            </div>
        <?php endif; ?>
        
        <div class="status-section">
            <h3 style="margin-bottom: 15px; color: #333;">Connection Status</h3>
            <div class="status-item">
                <span class="status-label">Database Host:</span>
                <span class="status-value <?php echo $connection_color; ?>">
                    <?php echo htmlspecialchars($db_host); ?>
                </span>
            </div>
            <div class="status-item">
                <span class="status-label">Database User:</span>
                <span class="status-value <?php echo $connection_color; ?>">
                    <?php echo htmlspecialchars($db_user); ?>
                </span>
            </div>
            <div class="status-item">
                <span class="status-label">Connection Status:</span>
                <span class="status-value <?php echo $connection_color; ?>">
                    <?php echo $connection_status; ?>
                </span>
            </div>
        </div>
        
        <?php if (!$setup_complete): ?>
            <form method="POST" class="form-section">
                <div class="credentials">
                    <strong>ℹ️ Database Configuration</strong>
                    <p><strong>Host:</strong> <?php echo htmlspecialchars($db_host); ?></p>
                    <p><strong>User:</strong> <?php echo htmlspecialchars($db_user); ?></p>
                    <p><strong>Port:</strong> <?php echo htmlspecialchars($db_port); ?></p>
                </div>
                
                <p style="margin-bottom: 20px; color: #666; font-size: 14px;">
                    Click the button below to automatically:<br>
                    ✓ Create the 'electronics_store' database<br>
                    ✓ Create all required tables<br>
                    ✓ Import the complete schema
                </p>
                
                <button type="submit" name="setup_database" value="1">
                    🚀 Initialize Database Now
                </button>
            </form>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding-top: 30px; border-top: 1px solid #ddd; text-align: center; color: #999; font-size: 12px;">
            <p>Electronics Store v1.0</p>
            <p>Setup Script - Remove after initialization</p>
        </div>
    </div>
</body>
</html>

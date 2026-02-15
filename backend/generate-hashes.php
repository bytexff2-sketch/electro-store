<?php
/**
 * Generate bcrypt password hashes for default users
 * Run this script in terminal: php generate-hashes.php
 */

$passwords = [
    'admin123' => 'admin',
    'customer123' => 'customer'
];

echo "Password Hashes for SQL:\n";
echo "=======================\n\n";

foreach ($passwords as $password => $user) {
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    echo "Username: {$user}\n";
    echo "Password: {$password}\n";
    echo "Hash: {$hash}\n";
    echo "---\n\n";
}
?>

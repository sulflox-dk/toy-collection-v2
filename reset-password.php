<?php

/**
 * Toy Collection V2 — Reset User Password
 *
 * Usage:
 *   php reset-password.php
 *
 * Resets an existing user's password in the users table.
 * You will be prompted for their email and a new password.
 */

if (php_sapi_name() !== 'cli') {
    echo "This script can only be run from the command line.\n";
    exit(1);
}

require_once __DIR__ . '/bootstrap/autoload.php';

use App\Kernel\Database\Database;

// Ensure users table exists
$db = Database::getInstance();
try {
    $db->query("SELECT 1 FROM users LIMIT 1");
} catch (\Exception $e) {
    echo "Error: The 'users' table does not exist. Run 'php migrate.php' first.\n";
    exit(1);
}

echo "=== Reset User Password ===\n\n";

// Email
echo "Email: ";
$email = trim(fgets(STDIN));
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Error: A valid email address is required.\n";
    exit(1);
}

// Look up the user
$user = $db->query("SELECT id, name FROM users WHERE email = ?", [$email])->fetch();
if (!$user) {
    echo "Error: No user found with that email.\n";
    exit(1);
}

// Password
echo "New password (min 8 characters): ";

// Try to hide password input on supported systems
if (function_exists('readline')) {
    system('stty -echo 2>/dev/null');
    $password = trim(fgets(STDIN));
    system('stty echo 2>/dev/null');
    echo "\n";
} else {
    $password = trim(fgets(STDIN));
}

if (strlen($password) < 8) {
    echo "Error: Password must be at least 8 characters.\n";
    exit(1);
}

// Update the user's password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$db->query(
    "UPDATE users SET password = ? WHERE id = ?",
    [$hashedPassword, $user['id']]
);

echo "\nPassword reset successfully!\n";
echo "  ID:    {$user['id']}\n";
echo "  Name:  {$user['name']}\n";
echo "  Email: {$email}\n";

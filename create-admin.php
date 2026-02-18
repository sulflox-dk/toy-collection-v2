<?php

/**
 * Toy Collection V2 — Create Admin User
 *
 * Usage:
 *   php create-admin.php
 *
 * Creates an admin user in the users table.
 * You will be prompted for name, email, and password.
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

echo "=== Create Admin User ===\n\n";

// Name
echo "Name: ";
$name = trim(fgets(STDIN));
if ($name === '') {
    echo "Error: Name is required.\n";
    exit(1);
}

// Email
echo "Email: ";
$email = trim(fgets(STDIN));
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Error: A valid email address is required.\n";
    exit(1);
}

// Check for existing user
$existing = $db->query("SELECT id FROM users WHERE email = ?", [$email])->fetch();
if ($existing) {
    echo "Error: A user with this email already exists.\n";
    exit(1);
}

// Password
echo "Password (min 8 characters): ";

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

// Create user
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$db->query(
    "INSERT INTO users (name, email, password) VALUES (?, ?, ?)",
    [$name, $email, $hashedPassword]
);

$userId = $db->lastInsertId();

echo "\nAdmin user created successfully!\n";
echo "  ID:    {$userId}\n";
echo "  Name:  {$name}\n";
echo "  Email: {$email}\n";

<?php
// 1. Security Check
if (php_sapi_name() !== 'cli') {
    die('⛔ This script can only be run from the command line.');
}

// 2. Bootstrap (Load classes)
require_once __DIR__ . '/bootstrap/autoload.php';

use App\Kernel\Database\Database;
use App\Kernel\Database\Migrator;

echo "\n🚀 Toy Tracker V2 Migration Tool\n";
echo "================================\n";

try {
    // Initialize Database and Migrator
    $db = Database::getInstance();
    $migrator = new Migrator($db, __DIR__ . '/database/migrations');

    // Parse Command
    // Usage: "php migrate.php" or "php migrate.php rollback"
    $command = $argv[1] ?? 'migrate';

    if ($command === 'rollback') {
        echo "🔄 Rolling back last batch...\n";
        $migrator->rollback();
    } else {
        $migrator->migrate();
    }

} catch (Exception $e) {
    echo "❌ Critical Error: " . $e->getMessage() . "\n";
    exit(1);
}
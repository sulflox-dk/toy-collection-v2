<?php

/**
 * Toy Collection V2 — Migration Runner
 *
 * Usage:
 *   php migrate.php              Run all pending migrations
 *   php migrate.php status       Show migration status
 *   php migrate.php rollback     Roll back the last batch
 */

// 1. CLI-only access
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('This script can only be run from the command line.');
}

// 2. Bootstrap
require_once __DIR__ . '/bootstrap/autoload.php';

use App\Kernel\Database\Database;
use App\Kernel\Database\Migrator;

echo "\n  Toy Collection V2 — Migration Tool\n";
echo "  ===================================\n\n";

try {
    $db = Database::getInstance();
    $migrator = new Migrator($db, __DIR__ . '/database/migrations');

    $command = $argv[1] ?? 'migrate';

    match ($command) {
        'migrate'  => $migrator->migrate(),
        'status'   => $migrator->status(),
        'rollback' => $migrator->rollback(),
        default    => fwrite(STDERR,
            "  Unknown command: '{$command}'\n\n"
            . "  Usage:\n"
            . "    php migrate.php              Run pending migrations\n"
            . "    php migrate.php status       Show migration status\n"
            . "    php migrate.php rollback     Roll back last batch\n\n"
        ),
    };

} catch (Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

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

require __DIR__ . '/bootstrap/autoload.php';

use App\Kernel\Database\Database;

// ── Helpers ───────────────────────────────────────────────────────

/**
 * Split a multi-statement SQL string into individual statements.
 * Strips empty entries and SQL comments.
 */
function splitStatements(string $sql): array
{
    $statements = [];
    foreach (explode(';', $sql) as $part) {
        $trimmed = trim($part);
        if ($trimmed !== '' && !str_starts_with($trimmed, '--')) {
            $statements[] = $trimmed;
        }
    }
    return $statements;
}

// ── Main ──────────────────────────────────────────────────────────

$command = $argv[1] ?? 'migrate';

echo "\n  Toy Collection V2 — Migration Tool\n";
echo "  ===================================\n\n";

try {
    $db  = Database::getInstance();
    $pdo = $db->getConnection();
    echo "  Database connected.\n\n";
} catch (\Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// Ensure the migrations table exists (with batch tracking)
$pdo->exec("CREATE TABLE IF NOT EXISTS `migrations` (
    `id`         int AUTO_INCREMENT PRIMARY KEY,
    `migration`  varchar(255) NOT NULL,
    `batch`      int NOT NULL DEFAULT 1,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
)");

// Add the batch column if upgrading from the old schema
$cols = $pdo->query("SHOW COLUMNS FROM `migrations`")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('batch', $cols)) {
    $pdo->exec("ALTER TABLE `migrations` ADD COLUMN `batch` int NOT NULL DEFAULT 1 AFTER `migration`");
}

// ── Command: status ───────────────────────────────────────────────

if ($command === 'status') {
    $executed = $pdo->query("SELECT migration, batch, created_at FROM migrations ORDER BY id")
                    ->fetchAll(PDO::FETCH_ASSOC);
    $files = glob(__DIR__ . '/database/migrations/*.php');
    sort($files);

    $executedNames = array_column($executed, 'migration');

    echo "  Migration              Batch   Ran at\n";
    echo "  " . str_repeat('-', 68) . "\n";

    foreach ($files as $file) {
        $name = basename($file);
        $key  = array_search($name, $executedNames);

        if ($key !== false) {
            $row = $executed[$key];
            printf("  %-36s %3d     %s\n", $name, $row['batch'], $row['created_at']);
        } else {
            printf("  %-36s  --     (pending)\n", $name);
        }
    }

    echo "\n";
    exit(0);
}

// ── Command: rollback ─────────────────────────────────────────────

if ($command === 'rollback') {
    $lastBatch = $pdo->query("SELECT MAX(batch) FROM migrations")->fetchColumn();

    if (!$lastBatch) {
        echo "  Nothing to roll back.\n\n";
        exit(0);
    }

    $rows = $pdo->query(
        "SELECT migration FROM migrations WHERE batch = {$lastBatch} ORDER BY id DESC"
    )->fetchAll(PDO::FETCH_COLUMN);

    echo "  Rolling back batch {$lastBatch} (" . count($rows) . " migration(s))...\n\n";

    foreach ($rows as $filename) {
        $filePath = __DIR__ . '/database/migrations/' . $filename;

        if (!file_exists($filePath)) {
            echo "  WARNING: File not found: {$filename} — skipping.\n";
            continue;
        }

        $migration = require $filePath;

        if (!is_array($migration) || empty($migration['down'])) {
            echo "  WARNING: No 'down' key in {$filename} — skipping.\n";
            continue;
        }

        echo "  Rolling back: {$filename}... ";

        try {
            $statements = splitStatements($migration['down']);

            foreach ($statements as $stmt) {
                $pdo->exec($stmt);
            }

            $pdo->prepare("DELETE FROM migrations WHERE migration = ? AND batch = ?")
                ->execute([$filename, $lastBatch]);

            echo "OK\n";
        } catch (\Exception $e) {
            echo "FAILED\n";
            echo "  Error: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    echo "\n  Rollback complete.\n\n";
    exit(0);
}

// ── Command: migrate (default) ────────────────────────────────────

$executedMigrations = $pdo->query("SELECT migration FROM migrations")
                          ->fetchAll(PDO::FETCH_COLUMN);

$lastBatch = (int) ($pdo->query("SELECT MAX(batch) FROM migrations")->fetchColumn() ?? 0);
$newBatch  = $lastBatch + 1;

$files = glob(__DIR__ . '/database/migrations/*.php');
sort($files);

$newMigrations = 0;

foreach ($files as $file) {
    $filename = basename($file);

    if (in_array($filename, $executedMigrations)) {
        continue;
    }

    echo "  Running: {$filename}... ";

    try {
        $migration = require $file;

        if (is_array($migration) && isset($migration['up'])) {
            $sql = $migration['up'];
        } elseif (is_string($migration)) {
            $sql = $migration;
        } else {
            echo "SKIPPED (invalid format)\n";
            continue;
        }

        // Execute each statement individually so a failure mid-file
        // doesn't leave the database in an unknown state.
        $statements = splitStatements($sql);

        foreach ($statements as $stmt) {
            $pdo->exec($stmt);
        }

        // Record the migration
        $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)")
            ->execute([$filename, $newBatch]);

        echo "OK\n";
        $newMigrations++;
    } catch (\Exception $e) {
        echo "FAILED\n";
        echo "  Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "\n";

if ($newMigrations === 0) {
    echo "  Nothing to migrate. Database is up to date.\n";
} else {
    echo "  Migrated {$newMigrations} file(s) in batch {$newBatch}.\n";
}

echo "\n";

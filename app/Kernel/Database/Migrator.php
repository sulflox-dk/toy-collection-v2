<?php

namespace App\Kernel\Database;

use PDO;
use RuntimeException;

class Migrator
{
    private Database $db;
    private PDO $pdo;
    private string $migrationsPath;

    public function __construct(Database $db, string $migrationsPath)
    {
        $this->db = $db;
        $this->pdo = $db->getConnection();
        $this->migrationsPath = $migrationsPath;

        $this->ensureMigrationsTableExists();
    }

    /**
     * Ensure the migrations table exists
     */
    private function ensureMigrationsTableExists(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS `migrations` (
            `id` int AUTO_INCREMENT PRIMARY KEY,
            `migration` varchar(255) NOT NULL,
            `batch` int NOT NULL DEFAULT 1,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
        )");
    }

    /**
     * Run all pending migrations
     */
    public function migrate(): void
    {
        $appliedMigrations = $this->getAppliedMigrations();
        $files = $this->getMigrationFiles();
        $pending = array_diff($files, $appliedMigrations);

        if (empty($pending)) {
            echo "  Nothing to migrate. Database is up to date.\n";
            return;
        }

        $batch = $this->getNextBatchNumber();

        foreach ($pending as $file) {
            echo "  Running: {$file}... ";

            $migration = require $this->migrationsPath . '/' . $file;

            if (is_array($migration) && isset($migration['up'])) {
                $sql = $migration['up'];
            } elseif (is_string($migration)) {
                $sql = $migration;
            } else {
                echo "SKIPPED (invalid format)\n";
                continue;
            }

            $statements = $this->splitStatements($sql);

            foreach ($statements as $stmt) {
                $this->pdo->exec($stmt);
            }

            $this->recordMigration($file, $batch);
            echo "OK\n";
        }

        echo "\n  Migrated " . count($pending) . " file(s) in batch {$batch}.\n";
    }

    /**
     * Roll back the last batch of migrations
     */
    public function rollback(): void
    {
        $lastBatch = $this->getLastBatchNumber();

        if (!$lastBatch) {
            echo "  Nothing to roll back.\n";
            return;
        }

        $stmt = $this->pdo->prepare(
            "SELECT migration FROM migrations WHERE batch = ? ORDER BY id DESC"
        );
        $stmt->execute([$lastBatch]);
        $migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo "  Rolling back batch {$lastBatch} (" . count($migrations) . " migration(s))...\n\n";

        foreach ($migrations as $file) {
            $filePath = $this->migrationsPath . '/' . $file;

            if (!file_exists($filePath)) {
                throw new RuntimeException("Migration file not found: {$file}");
            }

            $migration = require $filePath;

            if (!is_array($migration) || empty($migration['down'])) {
                throw new RuntimeException(
                    "Cannot roll back '{$file}': no 'down' definition. "
                    . "Add a 'down' key to the migration or resolve manually."
                );
            }

            echo "  Rolling back: {$file}... ";

            $statements = $this->splitStatements($migration['down']);

            foreach ($statements as $stmt) {
                $this->pdo->exec($stmt);
            }

            $this->removeMigrationRecord($file, $lastBatch);
            echo "OK\n";
        }

        echo "\n  Rollback complete.\n";
    }

    /**
     * Show the status of all migrations
     */
    public function status(): void
    {
        $executed = $this->pdo->query(
            "SELECT migration, batch, created_at FROM migrations ORDER BY id"
        )->fetchAll(PDO::FETCH_ASSOC);

        $executedNames = array_column($executed, 'migration');
        $files = $this->getMigrationFiles();

        echo "  Migration                                  Batch   Ran at\n";
        echo "  " . str_repeat('-', 68) . "\n";

        foreach ($files as $name) {
            $key = array_search($name, $executedNames);

            if ($key !== false) {
                $row = $executed[$key];
                printf("  %-42s %3d     %s\n", $name, $row['batch'], $row['created_at']);
            } else {
                printf("  %-42s  --     (pending)\n", $name);
            }
        }

        echo "\n";
    }

    // ── Private helpers ───────────────────────────────────────────

    private function getAppliedMigrations(): array
    {
        $stmt = $this->pdo->query("SELECT migration FROM migrations");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function getMigrationFiles(): array
    {
        $files = glob($this->migrationsPath . '/*.php');
        $files = array_map('basename', $files);
        sort($files);
        return $files;
    }

    private function getNextBatchNumber(): int
    {
        return $this->getLastBatchNumber() + 1;
    }

    private function getLastBatchNumber(): int
    {
        return (int) ($this->pdo->query("SELECT MAX(batch) FROM migrations")->fetchColumn() ?? 0);
    }

    private function recordMigration(string $file, int $batch): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
        $stmt->execute([$file, $batch]);
    }

    private function removeMigrationRecord(string $file, int $batch): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM migrations WHERE migration = ? AND batch = ?");
        $stmt->execute([$file, $batch]);
    }

    /**
     * Split a multi-statement SQL string into individual statements.
     * Prevents half-migrated state — if statement N fails, we know
     * exactly which statements already committed (DDL auto-commits in MySQL).
     */
    private function splitStatements(string $sql): array
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
}

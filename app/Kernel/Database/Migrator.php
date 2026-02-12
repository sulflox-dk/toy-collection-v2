<?php
namespace App\Kernel\Database;

use Exception;
use PDO;

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
            `migration` varchar(255),
            `batch` int,
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
            echo "✨ Nothing to migrate.\n";
            return;
        }

        // Calculate next batch number
        $stmt = $this->pdo->query("SELECT MAX(batch) FROM migrations");
        $batch = ($stmt->fetchColumn() ?? 0) + 1;

        foreach ($pending as $file) {
            echo "Running: $file... ";
            
            try {
                $migration = require $this->migrationsPath . '/' . $file;
                
                // Handle different migration formats (array or raw SQL)
                if (is_array($migration) && isset($migration['up'])) {
                    $this->pdo->exec($migration['up']);
                } elseif (is_string($migration)) {
                    $this->pdo->exec($migration);
                }

                // Log to DB
                $stmt = $this->pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
                $stmt->execute([$file, $batch]);

                echo "✅ Done\n";
            } catch (Exception $e) {
                echo "❌ Failed! " . $e->getMessage() . "\n";
                exit(1);
            }
        }
        
        echo "\n🎉 Migration completed successfully!\n";
    }

    /**
     * Rollback the last batch of migrations
     */
    public function rollback(): void
    {
        // Get last batch number
        $stmt = $this->pdo->query("SELECT MAX(batch) FROM migrations");
        $lastBatch = $stmt->fetchColumn();

        if (!$lastBatch) {
            echo "✨ Nothing to rollback.\n";
            return;
        }

        // Get migrations in that batch (in reverse order)
        $stmt = $this->pdo->prepare("SELECT migration FROM migrations WHERE batch = ? ORDER BY id DESC");
        $stmt->execute([$lastBatch]);
        $migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($migrations as $file) {
            echo "Rolling back: $file... ";

            try {
                $migration = require $this->migrationsPath . '/' . $file;

                if (is_array($migration) && isset($migration['down'])) {
                    $this->pdo->exec($migration['down']);
                } else {
                    // If it was just a string or missing 'down', we can't rollback safely
                    echo "⚠️ Skipped (No 'down' definition)\n";
                    // We still delete the record though, assuming manual fix
                }

                // Remove from DB
                $del = $this->pdo->prepare("DELETE FROM migrations WHERE migration = ?");
                $del->execute([$file]);

                echo "✅ Done\n";
            } catch (Exception $e) {
                echo "❌ Failed! " . $e->getMessage() . "\n";
                exit(1);
            }
        }
    }

    private function getAppliedMigrations(): array
    {
        $stmt = $this->pdo->query("SELECT migration FROM migrations");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function getMigrationFiles(): array
    {
        $files = glob($this->migrationsPath . '/*.php');
        return array_map('basename', $files);
    }
}
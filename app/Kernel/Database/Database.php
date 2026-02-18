<?php

namespace App\Kernel\Database;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

class Database
{
    /**
     * @var Database|null
     */
    private static $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        // 1. SAFE ENV LOADING (Kept from our previous fix)
        // We rely on autoload.php to have populated env vars.
        $host    = getenv('DB_HOST') ?: $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port    = getenv('DB_PORT') ?: $_ENV['DB_PORT'] ?? '3306';
        $dbName  = getenv('DB_DATABASE') ?: $_ENV['DB_DATABASE'] ?? '';
        $user    = getenv('DB_USERNAME') ?: $_ENV['DB_USERNAME'] ?? 'root';
        $pass    = getenv('DB_PASSWORD') ?: $_ENV['DB_PASSWORD'] ?? '';
        $charset = getenv('DB_CHARSET') ?: $_ENV['DB_CHARSET'] ?? 'utf8mb4';

        if (empty($dbName)) {
            throw new RuntimeException('Database name (DB_DATABASE) is not set in the environment.');
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed: ' . $e->getMessage());
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Returns the raw PDO connection.
     * Restored for compatibility.
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Alias for getConnection, used in newer code.
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    // ── Query helpers (Restored functionality) ────────────────────────

    /**
     * Prepare and execute a statement with Smart Binding.
     * Restored logic to handle INT/BOOL/NULL correctly.
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            
            foreach ($params as $key => $value) {
                // PDO parameters are 1-indexed if using ? placeholders
                $paramKey = is_int($key) ? $key + 1 : $key;
                
                // --- SMART BINDING 🧠 ---
                $type = PDO::PARAM_STR;
                if (is_int($value)) {
                    $type = PDO::PARAM_INT;
                } elseif (is_bool($value)) {
                    $type = PDO::PARAM_BOOL;
                } elseif (is_null($value)) {
                    $type = PDO::PARAM_NULL;
                }

                $stmt->bindValue($paramKey, $value, $type);
            }
            
            $stmt->execute();
            return $stmt;

        } catch (PDOException $e) {
            throw new RuntimeException("Database Query Error: " . $e->getMessage());
        }
    }

    /**
     * Execute a SELECT and return all rows.
     * (Restored)
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Execute a SELECT and return a single row (or null).
     * (Restored)
     */
    public function fetch(string $sql, array $params = []): ?array
    {
        $row = $this->query($sql, $params)->fetch();
        return $row !== false ? $row : null;
    }

    /**
     * Execute an INSERT / UPDATE / DELETE and return the number of affected rows.
     * (Restored - This fixes your error!)
     */
    public function execute(string $sql, array $params = []): int
    {
        return $this->query($sql, $params)->rowCount();
    }

    /**
     * Return the last auto-increment ID after an INSERT.
     */
    public function lastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }

    // ── Transactions ──────────────────────────────────────────────

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    // ── Utilities ─────────────────────────────────────────────────

    public function getEnumValues(string $table, string $column): array
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table)) {
            throw new RuntimeException("Invalid table name: {$table}");
        }

        // Use our restored query method
        $stmt = $this->query("SHOW COLUMNS FROM `{$table}` LIKE ?", [$column]);
        $result = $stmt->fetch();

        if ($result && preg_match("/^enum\('(.*)'\)$/", $result['Type'], $matches)) {
            return explode("','", $matches[1]);
        }

        return [];
    }
}
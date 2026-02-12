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
        $envPath = ROOT_PATH . '/.env';

        if (!file_exists($envPath)) {
            throw new RuntimeException('.env file not found at: ' . $envPath);
        }

        $env = parse_ini_file($envPath);

        if ($env === false) {
            throw new RuntimeException('Failed to parse .env file');
        }

        $host    = $env['DB_HOST'] ?? '127.0.0.1';
        $port    = $env['DB_PORT'] ?? '3306';
        $dbName  = $env['DB_DATABASE'] ?? '';
        $charset = $env['DB_CHARSET'] ?? 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO(
                $dsn,
                $env['DB_USERNAME'] ?? 'root',
                $env['DB_PASSWORD'] ?? '',
                $options
            );
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Returns a cached instance of a Database object
     *
     * @return Database
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Returns the raw PDO connection for cases that need it directly
     * (e.g. the migration runner executing DDL).
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    // ── Query helpers ─────────────────────────────────────────────

    /**
     * Prepare and execute a statement, returning the PDOStatement.
     * Use this when you need cursor-level access (e.g. large result sets).
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new RuntimeException(
                'Query failed: ' . $e->getMessage() . $this->debugSql($sql),
                0,
                $e
            );
        }
    }

    /**
     * Execute a SELECT and return all rows.
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Execute a SELECT and return a single row (or null).
     */
    public function fetch(string $sql, array $params = []): ?array
    {
        $row = $this->query($sql, $params)->fetch();
        return $row !== false ? $row : null;
    }

    /**
     * Execute an INSERT / UPDATE / DELETE and return the number of affected rows.
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

    /**
     * Retrieve the allowed values for an ENUM column.
     * Table name is validated to prevent SQL injection (cannot be parameterized).
     */
    public function getEnumValues(string $table, string $column): array
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table)) {
            throw new RuntimeException("Invalid table name: {$table}");
        }

        $stmt = $this->query("SHOW COLUMNS FROM `{$table}` LIKE ?", [$column]);
        $result = $stmt->fetch();

        if ($result && preg_match("/^enum\('(.*)'\)$/", $result['Type'], $matches)) {
            return explode("','", $matches[1]);
        }

        return [];
    }

    // ── Internal ──────────────────────────────────────────────────

    /**
     * Append the SQL text to error messages only when APP_DEBUG is enabled.
     */
    private function debugSql(string $sql): string
    {
        $env = parse_ini_file(ROOT_PATH . '/.env');
        $debug = ($env['APP_DEBUG'] ?? 'false') === 'true';

        return $debug ? " [SQL: {$sql}]" : '';
    }
}

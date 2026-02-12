<?php
namespace App\Kernel\Database;

use PDO;
use PDOException;
use Exception;

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        // Simpel .env parser (til vi får en config loader)
        $envPath = __DIR__ . '/../../../.env';
        if (!file_exists($envPath)) {
            die("❌ .env file missing!");
        }
        $env = parse_ini_file($envPath);

        $dsn = "mysql:host=" . $env['DB_HOST'] . ";dbname=" . $env['DB_DATABASE'] . ";charset=" . $env['DB_CHARSET'];
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $env['DB_USERNAME'], $env['DB_PASSWORD'], $options);
        } catch (PDOException $e) {
            die("Database Connection Failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    // --- Helper Methods ---

    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            // Her kunne vi logge fejlen til storage/logs/db.log
            throw new Exception("SQL Error: " . $e->getMessage() . " [Query: $sql]");
        }
    }

    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    // --- Transactions (Fra Refactoring Guide) ---

    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    public function commit() {
        return $this->pdo->commit();
    }

    public function rollBack() {
        return $this->pdo->rollBack();
    }

    // --- Enum Helper (Fra Refactoring Guide) ---
    // Henter mulige værdier fra en ENUM kolonne
    public function getEnumValues($table, $column) {
        $stmt = $this->query("SHOW COLUMNS FROM `$table` LIKE ?", [$column]);
        $result = $stmt->fetch();
        
        if ($result) {
            preg_match("/^enum\(\'(.*)\'\)$/", $result['Type'], $matches);
            $enum = explode("','", $matches[1]);
            return $enum;
        }
        return [];
    }
}
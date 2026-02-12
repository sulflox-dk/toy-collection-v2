<?php
namespace App\Kernel\Database;

use App\Kernel\Database\Database;
use PDO;

abstract class BaseModel
{
    // The table name (children MUST override this)
    protected static string $table;

    /**
     * Get the database connection
     */
    protected static function db(): PDO
    {
        return Database::getInstance()->getConnection();
    }

    /**
     * Fetch all records from the table
     */
    public static function all(): array
    {
        $table = static::$table;
        $stmt = static::db()->query("SELECT * FROM `{$table}`");
        return $stmt->fetchAll();
    }

    /**
     * Find a single record by ID
     */
    public static function find(int $id)
    {
        $table = static::$table;
        $stmt = static::db()->prepare("SELECT * FROM `{$table}` WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Find records where a column matches a value
     */
    public static function where(string $column, $value): array
    {
        $table = static::$table;
        // Whitelist validation could be added here for extra safety, 
        // but parameter binding protects the value.
        $stmt = static::db()->prepare("SELECT * FROM `{$table}` WHERE `{$column}` = :value");
        $stmt->execute(['value' => $value]);
        return $stmt->fetchAll();
    }
    
    /**
     * Create a new record
     */
    public static function create(array $data): int
    {
        $table = static::$table;
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})";
        
        $stmt = static::db()->prepare($sql);
        $stmt->execute($data);
        
        return (int) static::db()->lastInsertId();
    }
    
    /**
     * Update a record
     */
    public static function update(int $id, array $data): bool
    {
        $table = static::$table;
        $setClause = [];
        foreach (array_keys($data) as $key) {
            $setClause[] = "{$key} = :{$key}";
        }
        $setClauseStr = implode(', ', $setClause);
        
        $sql = "UPDATE `{$table}` SET {$setClauseStr} WHERE id = :id";
        $data['id'] = $id;
        
        $stmt = static::db()->prepare($sql);
        return $stmt->execute($data);
    }
    
    /**
     * Delete a record
     */
    public static function delete(int $id): bool
    {
        $table = static::$table;
        $stmt = static::db()->prepare("DELETE FROM `{$table}` WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
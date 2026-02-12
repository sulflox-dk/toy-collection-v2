<?php
namespace App\Kernel\Database;

use RuntimeException;

abstract class BaseModel
{
    // The table name (children MUST override this)
    protected static string $table;

    /**
     * Get the Database wrapper (provides query helpers + error handling).
     */
    protected static function db(): Database
    {
        return Database::getInstance();
    }

    // ── Reads ─────────────────────────────────────────────────────

    /**
     * Fetch all records from the table.
     */
    public static function all(): array
    {
        $table = static::$table;
        return static::db()->fetchAll("SELECT * FROM `{$table}`");
    }

    /**
     * Find a single record by ID, or null if it doesn't exist.
     */
    public static function find(int $id): ?array
    {
        $table = static::$table;
        return static::db()->fetch(
            "SELECT * FROM `{$table}` WHERE `id` = ? LIMIT 1",
            [$id]
        );
    }

    /**
     * Find a single record by ID or throw.
     */
    public static function findOrFail(int $id): array
    {
        $row = static::find($id);

        if ($row === null) {
            throw new RuntimeException(
                static::$table . " record #{$id} not found"
            );
        }

        return $row;
    }

    /**
     * Find all records where a column matches a value.
     */
    public static function where(string $column, mixed $value): array
    {
        static::validateIdentifier($column);
        $table = static::$table;
        return static::db()->fetchAll(
            "SELECT * FROM `{$table}` WHERE `{$column}` = ?",
            [$value]
        );
    }

    /**
     * Find the first record where a column matches a value, or null.
     */
    public static function firstWhere(string $column, mixed $value): ?array
    {
        static::validateIdentifier($column);
        $table = static::$table;
        return static::db()->fetch(
            "SELECT * FROM `{$table}` WHERE `{$column}` = ? LIMIT 1",
            [$value]
        );
    }

    /**
     * Return an array of values for a single column.
     *
     *   Manufacturer::pluck('name')  →  ['Kenner', 'Hasbro']
     */
    public static function pluck(string $column): array
    {
        static::validateIdentifier($column);
        $table = static::$table;
        $rows = static::db()->fetchAll("SELECT `{$column}` FROM `{$table}`");
        return array_column($rows, $column);
    }

    /**
     * Count records (optionally filtered by a column value).
     */
    public static function count(?string $column = null, mixed $value = null): int
    {
        $table = static::$table;

        if ($column !== null) {
            static::validateIdentifier($column);
            $row = static::db()->fetch(
                "SELECT COUNT(*) AS cnt FROM `{$table}` WHERE `{$column}` = ?",
                [$value]
            );
        } else {
            $row = static::db()->fetch("SELECT COUNT(*) AS cnt FROM `{$table}`");
        }

        return (int) $row['cnt'];
    }

    /**
     * Check whether a record with the given ID exists.
     */
    public static function exists(int $id): bool
    {
        $table = static::$table;
        $row = static::db()->fetch(
            "SELECT 1 FROM `{$table}` WHERE `id` = ? LIMIT 1",
            [$id]
        );
        return $row !== null;
    }

    // ── Writes ────────────────────────────────────────────────────

    /**
     * Create a new record and return its ID.
     */
    public static function create(array $data): int
    {
        $table = static::$table;
        $columns = [];
        $placeholders = [];

        foreach (array_keys($data) as $key) {
            static::validateIdentifier($key);
            $columns[] = "`{$key}`";
            $placeholders[] = '?';
        }

        $sql = sprintf(
            "INSERT INTO `%s` (%s) VALUES (%s)",
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        static::db()->execute($sql, array_values($data));
        return static::db()->lastInsertId();
    }

    /**
     * Update a record by ID. Returns true on success.
     */
    public static function update(int $id, array $data): bool
    {
        $table = static::$table;
        $setClauses = [];
        $params = [];

        foreach ($data as $key => $value) {
            static::validateIdentifier($key);
            $setClauses[] = "`{$key}` = ?";
            $params[] = $value;
        }

        $params[] = $id;

        $sql = sprintf(
            "UPDATE `%s` SET %s WHERE `id` = ?",
            $table,
            implode(', ', $setClauses)
        );

        static::db()->execute($sql, $params);
        return true;
    }

    /**
     * Delete a record by ID. Returns true on success.
     */
    public static function delete(int $id): bool
    {
        $table = static::$table;
        static::db()->execute("DELETE FROM `{$table}` WHERE `id` = ?", [$id]);
        return true;
    }

    // ── Internal ──────────────────────────────────────────────────

    /**
     * Ensure a column name is a safe SQL identifier.
     * Prevents injection through array keys or dynamic column names.
     */
    private static function validateIdentifier(string $name): void
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name)) {
            throw new RuntimeException("Invalid column name: '{$name}'");
        }
    }
}

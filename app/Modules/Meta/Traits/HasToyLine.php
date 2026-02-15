<?php
namespace App\Modules\Meta\Traits;

trait HasToyLine
{
    // Force the model to define its table name
    abstract public static function getTableName(): string;

    /**
     * Count records by Toy Line ID.
     */
    public static function countByToyLine(int $toyLineId): int
    {
        $table = static::getTableName();
        
        $sql = "SELECT COUNT(*) FROM {$table} WHERE toy_line_id = ?";
        return (int) static::db()->query($sql, [$toyLineId])->fetchColumn();
    }

    /**
     * Reassign records from one Toy Line to another.
     */
    public static function migrateToyLine(int $oldId, int $newId): void
    {
        $table = static::getTableName();
        
        $sql = "UPDATE {$table} SET toy_line_id = ? WHERE toy_line_id = ?";
        static::db()->query($sql, [$newId, $oldId]);
    }
}
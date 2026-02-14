<?php
namespace App\Modules\Meta\Traits;

trait HasUniverse
{
    // Tell the IDE that the class using this trait must implement this method
    abstract public static function getTableName(): string;

    /**
     * Count records by universe ID.
     */
    public static function countByUniverse(int $universeId): int
    {
        // Ensure the table name is retrieved from the model
        $table = static::getTableName();
        
        // Check if the column exists could be done here, but usually we assume 
        // models using this trait HAVE the column.
        $sql = "SELECT COUNT(*) FROM {$table} WHERE universe_id = ?";
        return (int) static::db()->query($sql, [$universeId])->fetchColumn();
    }

    /**
     * Reassign records from one universe to another.
     */
    public static function migrateUniverse(int $oldId, int $newId): void
    {
        $table = static::getTableName();
        
        $sql = "UPDATE {$table} SET universe_id = ? WHERE universe_id = ?";
        static::db()->query($sql, [$newId, $oldId]);
    }
}
<?php
namespace App\Modules\Meta\Traits;

trait HasManufacturer
{
    // Tell the IDE that the class using this trait must implement this method
    abstract public static function getTableName(): string;

    /**
     * Count records by manufacturer ID.
     */
    public static function countByManufacturer(int $manufacturerId): int
    {
        // Use the accessor method instead of the property
        $table = static::getTableName();
        
        $sql = "SELECT COUNT(*) FROM {$table} WHERE manufacturer_id = ?";
        return (int) static::db()->query($sql, [$manufacturerId])->fetchColumn();
    }

    /**
     * Reassign records from one manufacturer to another.
     */
    public static function migrateManufacturer(int $oldId, int $newId): void
    {
        $table = static::getTableName();
        
        $sql = "UPDATE {$table} SET manufacturer_id = ? WHERE manufacturer_id = ?";
        static::db()->query($sql, [$newId, $oldId]);
    }
}
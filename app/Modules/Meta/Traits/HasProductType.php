<?php
namespace App\Modules\Meta\Traits;

trait HasProductType
{
    abstract public static function getTableName(): string;

    /**
     * Count records by product type ID.
     */
    public static function countByProductType(int $typeId): int
    {
        $table = static::getTableName();
        // Ensure column exists in your schema before using this
        $sql = "SELECT COUNT(*) FROM {$table} WHERE product_type_id = ?";
        return (int) static::db()->query($sql, [$typeId])->fetchColumn();
    }

    /**
     * Reassign records from one product type to another.
     */
    public static function migrateProductType(int $oldId, int $newId): void
    {
        $table = static::getTableName();
        $sql = "UPDATE {$table} SET product_type_id = ? WHERE product_type_id = ?";
        static::db()->query($sql, [$newId, $oldId]);
    }
}
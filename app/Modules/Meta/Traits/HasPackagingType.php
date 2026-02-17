<?php
namespace App\Modules\Meta\Traits;

trait HasPackagingType
{
    abstract public static function getTableName(): string;

    public static function countByPackagingType(int $typeId): int
    {
        $table = static::getTableName();
        $sql = "SELECT COUNT(*) FROM {$table} WHERE packaging_type_id = ?";
        return (int) static::db()->query($sql, [$typeId])->fetchColumn();
    }

    public static function migratePackagingType(int $oldId, int $newId): void
    {
        $table = static::getTableName();
        $sql = "UPDATE {$table} SET packaging_type_id = ? WHERE packaging_type_id = ?";
        static::db()->query($sql, [$newId, $oldId]);
    }
}
<?php
namespace App\Modules\Meta\Traits;

trait HasEntertainmentSource
{
    abstract public static function getTableName(): string;

    public static function countByEntertainmentSource(int $sourceId): int
    {
        $table = static::getTableName();
        $sql = "SELECT COUNT(*) FROM {$table} WHERE entertainment_source_id = ?";
        return (int) static::db()->query($sql, [$sourceId])->fetchColumn();
    }

    public static function migrateEntertainmentSource(int $oldId, int $newId): void
    {
        $table = static::getTableName();
        $sql = "UPDATE {$table} SET entertainment_source_id = ? WHERE entertainment_source_id = ?";
        static::db()->query($sql, [$newId, $oldId]);
    }
}
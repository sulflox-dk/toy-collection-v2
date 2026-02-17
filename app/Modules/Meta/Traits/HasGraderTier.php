<?php
namespace App\Modules\Meta\Traits;

trait HasGraderTier
{
    abstract public static function getTableName(): string;

    public static function countByGraderTier(int $tierId): int
    {
        $table = static::getTableName();
        $sql = "SELECT COUNT(*) FROM {$table} WHERE grader_tier_id = ?";
        return (int) static::db()->query($sql, [$tierId])->fetchColumn();
    }

    public static function migrateGraderTier(int $oldId, int $newId): void
    {
        $table = static::getTableName();
        $sql = "UPDATE {$table} SET grader_tier_id = ? WHERE grader_tier_id = ?";
        static::db()->query($sql, [$newId, $oldId]);
    }
}
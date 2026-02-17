<?php
namespace App\Modules\Meta\Traits;

trait HasAcquisitionStatus
{
    abstract public static function getTableName(): string;

    public static function countByAcquisitionStatus(int $statusId): int
    {
        $table = static::getTableName();
        $sql = "SELECT COUNT(*) FROM {$table} WHERE acquisition_status_id = ?";
        return (int) static::db()->query($sql, [$statusId])->fetchColumn();
    }

    public static function migrateAcquisitionStatus(int $oldId, int $newId): void
    {
        $table = static::getTableName();
        $sql = "UPDATE {$table} SET acquisition_status_id = ? WHERE acquisition_status_id = ?";
        static::db()->query($sql, [$newId, $oldId]);
    }
}
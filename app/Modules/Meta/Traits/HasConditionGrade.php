<?php
namespace App\Modules\Meta\Traits;

trait HasConditionGrade
{
    abstract public static function getTableName(): string;

    public static function countByConditionGrade(int $gradeId): int
    {
        $table = static::getTableName();
        $sql = "SELECT COUNT(*) FROM {$table} WHERE condition_grade_id = ?";
        return (int) static::db()->query($sql, [$gradeId])->fetchColumn();
    }

    public static function migrateConditionGrade(int $oldId, int $newId): void
    {
        $table = static::getTableName();
        $sql = "UPDATE {$table} SET condition_grade_id = ? WHERE condition_grade_id = ?";
        static::db()->query($sql, [$newId, $oldId]);
    }
}
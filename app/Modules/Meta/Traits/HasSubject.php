<?php
namespace App\Modules\Meta\Traits;

trait HasSubject
{
    abstract public static function getTableName(): string;

    public static function countBySubject(int $subjectId): int
    {
        $table = static::getTableName();
        $sql = "SELECT COUNT(*) FROM {$table} WHERE subject_id = ?";
        return (int) static::db()->query($sql, [$subjectId])->fetchColumn();
    }

    public static function migrateSubject(int $oldId, int $newId): void
    {
        $table = static::getTableName();
        $sql = "UPDATE {$table} SET subject_id = ? WHERE subject_id = ?";
        static::db()->query($sql, [$newId, $oldId]);
    }
}
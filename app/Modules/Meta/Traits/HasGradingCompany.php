<?php
namespace App\Modules\Meta\Traits;

trait HasGradingCompany
{
    abstract public static function getTableName(): string;

    public static function countByGradingCompany(int $companyId): int
    {
        $table = static::getTableName();
        $sql = "SELECT COUNT(*) FROM {$table} WHERE grader_company_id = ?";
        return (int) static::db()->query($sql, [$companyId])->fetchColumn();
    }

    public static function migrateGradingCompany(int $oldId, int $newId): void
    {
        $table = static::getTableName();
        $sql = "UPDATE {$table} SET grader_company_id = ? WHERE grader_company_id = ?";
        static::db()->query($sql, [$newId, $oldId]);
    }
}
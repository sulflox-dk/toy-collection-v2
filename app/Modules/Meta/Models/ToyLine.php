<?php
namespace App\Modules\Meta\Models;

use App\Kernel\Database\BaseModel;

class ToyLine extends BaseModel
{
    protected static string $table = 'meta_toy_lines';

    public static function countByManufacturer(int $manufacturerId): int
    {
        $sql = "SELECT COUNT(*) FROM " . static::$table . " WHERE manufacturer_id = ?";
        return (int) static::db()->query($sql, [$manufacturerId])->fetchColumn();
    }

    public static function migrateManufacturer(int $oldId, int $newId): void
    {
        $sql = "UPDATE " . static::$table . " SET manufacturer_id = ? WHERE manufacturer_id = ?";
        static::db()->query($sql, [$newId, $oldId]);
    }
}
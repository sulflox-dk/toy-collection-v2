<?php
namespace App\Modules\Catalog\Models;

use App\Kernel\Database\BaseModel;

class CatalogToy extends BaseModel
{
    protected static string $table = 'catalog_toys';

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
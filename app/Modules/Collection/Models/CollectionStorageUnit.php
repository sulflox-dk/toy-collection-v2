<?php
namespace App\Modules\Collection\Models;

use App\Kernel\Database\BaseModel;

class CollectionStorageUnit extends BaseModel
{
    protected static string $table = 'collection_storage_units';

    public static function getPaginated(int $page = 1, int $perPage = 20, string $search = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $whereConditions = [];

        $sql = "SELECT * FROM " . static::$table;

        if ($search !== '') {
            $whereConditions[] = "(name LIKE ? OR box_code LIKE ? OR location LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }

        $sql .= " ORDER BY name ASC";

        $countSql = "SELECT COUNT(*) FROM ($sql) as sub";
        $total = (int) static::db()->query($countSql, $params)->fetchColumn();
        $totalPages = ceil($total / $perPage);

        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $items = static::db()->query($sql, $params)->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'items' => $items,
            'total' => $total,
            'totalPages' => $totalPages
        ];
    }
}
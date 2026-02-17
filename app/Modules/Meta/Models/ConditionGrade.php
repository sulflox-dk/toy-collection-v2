<?php
namespace App\Modules\Meta\Models;

use App\Kernel\Database\BaseModel;
use App\Kernel\Database\HasSlug;

class ConditionGrade extends BaseModel
{
    use HasSlug;

    protected static string $table = 'meta_condition_grades';

    public static function getPaginated(int $page = 1, int $perPage = 20, string $search = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $whereConditions = [];

        $sql = "SELECT * FROM " . static::$table;

        if ($search !== '') {
            $whereConditions[] = "(name LIKE ? OR abbreviation LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }

        // Sort by defined order first (C10 -> C1), then name
        $sql .= " ORDER BY sort_order ASC, name ASC";

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
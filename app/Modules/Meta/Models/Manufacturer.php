<?php
namespace App\Modules\Meta\Models;

use App\Kernel\Database\BaseModel;

class Manufacturer extends BaseModel
{
    protected static string $table = 'meta_manufacturers';

    public static function getPaginatedWithLineCount(int $page = 1, int $perPage = 10, string $search = '', string $visibility = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $whereConditions = []; // Array to hold dynamic WHERE clauses

        $sql = "
            SELECT 
                m.*, 
                COUNT(l.id) as lines_count 
            FROM " . static::$table . " m
            LEFT JOIN meta_toy_lines l ON m.id = l.manufacturer_id
        ";

        // 1. Apply Search Filter
        if ($search !== '') {
            $whereConditions[] = "m.name LIKE ?";
            $params[] = "%$search%";
        }

        // 2. Apply Visibility Filter
        if ($visibility !== '') {
            $whereConditions[] = "m.show_on_dashboard = ?";
            $params[] = (int) $visibility;
        }

        // 3. Append WHERE clauses if they exist
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }

        $sql .= " GROUP BY m.id ORDER BY m.name ASC";

        // Count Total Rows (using a subquery because of the GROUP BY)
        $countSql = "SELECT COUNT(*) FROM ($sql) as sub";
        $total = (int) static::db()->query($countSql, $params)->fetchColumn();
        $totalPages = ceil($total / $perPage);

        // Fetch the actual data
        $sql .= " LIMIT $perPage OFFSET $offset";
        $items = static::db()->query($sql, $params)->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'items' => $items,
            'total' => $total,
            'totalPages' => $totalPages
        ];
    }
}
<?php
namespace App\Modules\Meta\Models;

use App\Kernel\Database\BaseModel;
use App\Modules\Meta\Traits\HasUniverse;
use App\Kernel\Database\HasSlug;

class EntertainmentSource extends BaseModel
{
    use HasUniverse, HasSlug;

    protected static string $table = 'meta_entertainment_sources';

    /**
     * Get paginated list with Universe names joined
     */
    public static function getPaginatedWithDetails(
        int $page = 1, 
        int $perPage = 20, 
        string $search = '', 
        string $visibility = '',
        string $type = '',      // <--- Filter by Type
        int $universeId = 0
    ): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $whereConditions = [];

        // Select main columns + Universe Name
        $sql = "
            SELECT 
                e.*,
                u.name as universe_name
            FROM " . static::$table . " e
            LEFT JOIN meta_universes u ON e.universe_id = u.id
        ";

        if ($search !== '') {
            $whereConditions[] = "e.name LIKE ?";
            $params[] = "%$search%";
        }

        if ($visibility !== '') {
            $whereConditions[] = "e.show_on_dashboard = ?";
            $params[] = (int) $visibility;
        }

        if ($type !== '') {
            $whereConditions[] = "e.type = ?";
            $params[] = $type;
        }

        if ($universeId > 0) {
            $whereConditions[] = "e.universe_id = ?";
            $params[] = $universeId;
        }

        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }

        $sql .= " ORDER BY e.name ASC";

        // 1. Total Count
        $countSql = "SELECT COUNT(*) FROM ($sql) as sub";
        $total = (int) static::db()->query($countSql, $params)->fetchColumn();
        $totalPages = ceil($total / $perPage);

        // 2. Pagination
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
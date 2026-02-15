<?php
namespace App\Modules\Meta\Models;

use App\Kernel\Database\BaseModel;
use App\Modules\Meta\Traits\HasUniverse;
use App\Kernel\Database\HasSlug;

class Subject extends BaseModel
{
    use HasUniverse, HasSlug;

    protected static string $table = 'meta_subjects';

    /**
     * Get paginated list with Universe names joined
     */
    public static function getPaginatedWithDetails(
        int $page = 1, 
        int $perPage = 20, 
        string $search = '', 
        string $type = '',
        int $universeId = 0
    ): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $whereConditions = [];

        $sql = "
            SELECT 
                s.*,
                u.name as universe_name
            FROM " . static::$table . " s
            LEFT JOIN meta_universes u ON s.universe_id = u.id
        ";

        if ($search !== '') {
            $whereConditions[] = "s.name LIKE ?";
            $params[] = "%$search%";
        }

        if ($type !== '') {
            $whereConditions[] = "s.type = ?";
            $params[] = $type;
        }

        if ($universeId > 0) {
            $whereConditions[] = "s.universe_id = ?";
            $params[] = $universeId;
        }

        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }

        $sql .= " ORDER BY s.name ASC";

        // Count
        $countSql = "SELECT COUNT(*) FROM ($sql) as sub";
        $total = (int) static::db()->query($countSql, $params)->fetchColumn();
        $totalPages = ceil($total / $perPage);

        // Pagination
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
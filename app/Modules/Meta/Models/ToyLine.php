<?php
namespace App\Modules\Meta\Models;

use App\Kernel\Database\BaseModel;
use App\Modules\Meta\Traits\HasUniverse;
use App\Modules\Meta\Traits\HasManufacturer;
use App\Kernel\Database\HasSlug;

class ToyLine extends BaseModel
{
    use HasManufacturer, HasUniverse, HasSlug;

    protected static string $table = 'meta_toy_lines';

    /**
     * Get paginated list with filters
     */
    public static function getPaginatedWithDetails(
        int $page = 1, 
        int $perPage = 20, 
        string $search = '', 
        string $visibility = '',
        int $manufacturerId = 0, // <--- New Param
        int $universeId = 0      // <--- New Param
    ): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $whereConditions = [];

        // Select main columns + joined names
        $sql = "
            SELECT 
                t.*,
                m.name as manufacturer_name,
                u.name as universe_name
            FROM " . static::$table . " t
            LEFT JOIN meta_manufacturers m ON t.manufacturer_id = m.id
            LEFT JOIN meta_universes u ON t.universe_id = u.id
        ";

        if ($search !== '') {
            $whereConditions[] = "t.name LIKE ?";
            $params[] = "%$search%";
        }

        if ($visibility !== '') {
            $whereConditions[] = "t.show_on_dashboard = ?";
            $params[] = (int) $visibility;
        }

        // --- NEW FILTERS ---
        if ($manufacturerId > 0) {
            $whereConditions[] = "t.manufacturer_id = ?";
            $params[] = $manufacturerId;
        }

        if ($universeId > 0) {
            $whereConditions[] = "t.universe_id = ?";
            $params[] = $universeId;
        }
        // -------------------

        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }

        $sql .= " ORDER BY t.name ASC";

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
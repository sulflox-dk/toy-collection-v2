<?php
namespace App\Modules\Meta\Models;

use App\Kernel\Database\BaseModel;
use App\Kernel\Database\HasSlug;

class Manufacturer extends BaseModel
{
    use HasSlug;
    protected static string $table = 'meta_manufacturers';

    public static function getPaginatedWithLineCount(int $page = 1, int $perPage = 20, string $search = '', string $visibility = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $whereConditions = [];

        $sql = "
            SELECT
                m.*,
                COUNT(l.id) as lines_count,
                (SELECT f.filepath FROM media_links ml JOIN media_files f ON ml.media_file_id = f.id
                 WHERE ml.entity_type = 'manufacturers' AND ml.entity_id = m.id
                 ORDER BY ml.is_featured DESC, ml.sort_order ASC LIMIT 1) AS image_path
            FROM " . static::$table . " m
            LEFT JOIN meta_toy_lines l ON m.id = l.manufacturer_id
        ";

        if ($search !== '') {
            $whereConditions[] = "m.name LIKE ?";
            $params[] = "%$search%";
        }

        if ($visibility !== '') {
            $whereConditions[] = "m.show_on_dashboard = ?";
            $params[] = (int) $visibility;
        }

        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }

        $sql .= " GROUP BY m.id ORDER BY m.name ASC";

        // 1. Total Count (Keep using parameters here)
        $countSql = "SELECT COUNT(*) FROM ($sql) as sub";
        $total = (int) static::db()->query($countSql, $params)->fetchColumn();
        $totalPages = ceil($total / $perPage);

        // 2. FIXED: Parameterize LIMIT and OFFSET 🛡️
        $sql .= " LIMIT ? OFFSET ?";
        
        // Add them to the params array
        $params[] = $perPage;
        $params[] = $offset;

        // NOTE: If your Database::query method uses standard execute(), 
        // you might need to enable PDO::ATTR_EMULATE_PREPARES => false 
        // in your Database connection to allow ints in LIMIT.
        $items = static::db()->query($sql, $params)->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'items' => $items,
            'total' => $total,
            'totalPages' => $totalPages
        ];
    }
}
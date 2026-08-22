<?php
namespace App\Modules\Meta\Models;

use App\Kernel\Database\BaseModel;
use App\Kernel\Database\HasSlug;

class Universe extends BaseModel
{
    use HasSlug;
    protected static string $table = 'meta_universes';

    public static function getPaginatedWithLineCount(int $page = 1, int $perPage = 20, string $search = '', string $visibility = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $whereConditions = [];

        $sql = "
            SELECT
                u.*,
                COUNT(l.id) as lines_count,
                (SELECT f.filepath FROM media_links ml JOIN media_files f ON ml.media_file_id = f.id
                 WHERE ml.entity_type = 'universes' AND ml.entity_id = u.id
                 ORDER BY ml.is_featured DESC, ml.sort_order ASC LIMIT 1) AS image_path
            FROM " . static::$table . " u
            LEFT JOIN meta_toy_lines l ON u.id = l.universe_id
        ";

        if ($search !== '') {
            $whereConditions[] = "u.name LIKE ?";
            $params[] = "%$search%";
        }

        if ($visibility !== '') {
            $whereConditions[] = "u.show_on_dashboard = ?";
            $params[] = (int) $visibility;
        }

        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }

        $sql .= " GROUP BY u.id ORDER BY u.name ASC";

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
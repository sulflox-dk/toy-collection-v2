<?php
namespace App\Modules\Importer\Models;

use App\Kernel\Database\BaseModel;
use App\Kernel\Database\HasSlug;

class ImporterSource extends BaseModel
{
    use HasSlug;

    protected static string $table = 'importer_sources';

    public static function getPaginated(int $page = 1, int $perPage = 20, string $search = '', string $active = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $whereConditions = [];

        $sql = "
            SELECT
                s.*,
                COUNT(i.id) as item_count,
                MAX(i.last_imported_at) as last_activity
            FROM " . static::$table . " s
            LEFT JOIN importer_items i ON s.id = i.source_id
        ";

        if ($search !== '') {
            $whereConditions[] = "(s.name LIKE ? OR s.base_url LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($active !== '') {
            $whereConditions[] = "s.is_active = ?";
            $params[] = (int) $active;
        }

        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }

        $sql .= " GROUP BY s.id ORDER BY s.name ASC";

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

    /**
     * Find a source whose base_url matches the given URL.
     */
    public static function findByUrl(string $url): ?array
    {
        $sources = static::db()->fetchAll(
            "SELECT * FROM " . static::$table . " WHERE is_active = 1 ORDER BY name ASC"
        );

        foreach ($sources as $source) {
            if (strpos($url, $source['base_url']) !== false) {
                return $source;
            }
        }

        return null;
    }

    /**
     * Get all active sources for dropdowns.
     */
    public static function allActive(): array
    {
        return static::db()->fetchAll(
            "SELECT * FROM " . static::$table . " WHERE is_active = 1 ORDER BY name ASC"
        );
    }

    /**
     * Stats for dashboard cards.
     */
    public static function getStats(): array
    {
        return static::db()->fetchAll("
            SELECT s.id, s.name, s.base_url, s.driver_class, s.is_active,
                   COUNT(i.id) as imported_count,
                   MAX(i.last_imported_at) as last_activity
            FROM " . static::$table . " s
            LEFT JOIN importer_items i ON s.id = i.source_id
            GROUP BY s.id
            ORDER BY s.name ASC
        ");
    }
}

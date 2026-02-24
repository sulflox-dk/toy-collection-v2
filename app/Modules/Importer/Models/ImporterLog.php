<?php
namespace App\Modules\Importer\Models;

use App\Kernel\Database\BaseModel;

class ImporterLog extends BaseModel
{
    protected static string $table = 'importer_logs';

    /**
     * Write a log entry.
     */
    public static function log(int $sourceId, string $status, ?int $importerItemId, string $message): int
    {
        static::db()->execute(
            "INSERT INTO " . static::$table . " (source_id, importer_item_id, status, message)
             VALUES (?, ?, ?, ?)",
            [$sourceId, $importerItemId, $status, $message]
        );

        return static::db()->lastInsertId();
    }

    /**
     * Paginated list with source name.
     */
    public static function getPaginated(int $page = 1, int $perPage = 30, string $search = '', int $sourceId = 0, string $status = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $whereConditions = [];

        $sql = "
            SELECT
                l.*,
                s.name as source_name,
                ii.external_id,
                ii.external_url
            FROM " . static::$table . " l
            LEFT JOIN importer_sources s ON l.source_id = s.id
            LEFT JOIN importer_items ii ON l.importer_item_id = ii.id
        ";

        if ($search !== '') {
            $whereConditions[] = "(l.message LIKE ? OR ii.external_id LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($sourceId > 0) {
            $whereConditions[] = "l.source_id = ?";
            $params[] = $sourceId;
        }

        if ($status !== '') {
            $whereConditions[] = "l.status = ?";
            $params[] = $status;
        }

        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }

        $sql .= " ORDER BY l.created_at DESC";

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

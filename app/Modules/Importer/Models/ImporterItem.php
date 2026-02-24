<?php
namespace App\Modules\Importer\Models;

use App\Kernel\Database\BaseModel;

class ImporterItem extends BaseModel
{
    protected static string $table = 'importer_items';

    /**
     * Find an import item by source + external ID.
     */
    public static function findByExternal(int $sourceId, string $externalId): ?array
    {
        return static::db()->fetch(
            "SELECT * FROM " . static::$table . " WHERE source_id = ? AND external_id = ?",
            [$sourceId, $externalId]
        );
    }

    /**
     * Register (insert or update) an import link.
     * Returns the import item ID.
     */
    public static function registerImport(int $sourceId, ?int $catalogToyId, string $externalId, string $externalUrl): int
    {
        $existing = static::findByExternal($sourceId, $externalId);

        if ($existing) {
            $sql = "UPDATE " . static::$table . " SET last_imported_at = NOW()";
            $params = ['id' => $existing['id']];

            if ($catalogToyId !== null) {
                $sql .= ", catalog_toy_id = :ctid";
                $params['ctid'] = $catalogToyId;
            }

            $sql .= " WHERE id = :id";
            static::db()->execute($sql, $params);

            return (int) $existing['id'];
        }

        static::db()->execute(
            "INSERT INTO " . static::$table . " (source_id, catalog_toy_id, external_id, external_url, last_imported_at)
             VALUES (?, ?, ?, ?, NOW())",
            [$sourceId, $catalogToyId, $externalId, $externalUrl]
        );

        return static::db()->lastInsertId();
    }

    /**
     * Paginated list with source name and catalog toy name.
     */
    public static function getPaginated(int $page = 1, int $perPage = 20, string $search = '', int $sourceId = 0): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $whereConditions = [];

        $sql = "
            SELECT
                i.*,
                s.name as source_name,
                ct.name as catalog_toy_name
            FROM " . static::$table . " i
            LEFT JOIN importer_sources s ON i.source_id = s.id
            LEFT JOIN catalog_toys ct ON i.catalog_toy_id = ct.id
        ";

        if ($search !== '') {
            $whereConditions[] = "(i.external_id LIKE ? OR ct.name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($sourceId > 0) {
            $whereConditions[] = "i.source_id = ?";
            $params[] = $sourceId;
        }

        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }

        $sql .= " ORDER BY i.last_imported_at DESC";

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

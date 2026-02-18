<?php
namespace App\Modules\Media\Models;

use App\Kernel\Database\BaseModel;

class MediaFile extends BaseModel
{
    protected static string $table = 'media_files';

    public static function getPaginated(int $page = 1, int $perPage = 20, string $search = '', string $attachmentType = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $where = [];

        $sql = "SELECT * FROM " . static::$table;

        // 1. Search Filter
        if ($search !== '') {
            $where[] = "(title LIKE ? OR filename LIKE ? OR description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        // 2. Attachment Type Filter
        if ($attachmentType !== '') {
            if ($attachmentType === 'unattached') {
                $where[] = "NOT EXISTS (SELECT 1 FROM media_links WHERE media_file_id = " . static::$table . ".id)";
            } else {
                $typeMap = [
                    'catalog_toys'    => 'catalog_toys',
                    'collection_toys' => 'collection_toys',
                    'universes'       => 'universes',
                    'manufacturers'   => 'manufacturers',
                    'toy_lines'       => 'toy_lines',
                    'sources'         => 'sources'
                ];

                if (isset($typeMap[$attachmentType])) {
                    $where[] = "EXISTS (SELECT 1 FROM media_links WHERE media_file_id = " . static::$table . ".id AND entity_type = ?)";
                    $params[] = $typeMap[$attachmentType];
                }
            }
        }

        // 3. Apply Conditions
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY created_at DESC";

        // 4. Count total (using your subquery approach)
        $countSql = "SELECT COUNT(*) FROM ($sql) as sub";
        $total = (int) static::db()->query($countSql, $params)->fetchColumn();
        $totalPages = ceil($total / $perPage);

        // 5. Fetch items (adding Limit and Offset)
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $items = static::db()->query($sql, $params)->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'items'      => $items,
            'total'      => $total,
            'totalPages' => $totalPages
        ];
    }
    
    /**
     * Get usage count (how many things is this file attached to?)
     */
    public static function getUsageCount(int $id): int
    {
        $sql = "SELECT COUNT(*) FROM media_links WHERE media_file_id = ?";
        return (int) static::db()->query($sql, [$id])->fetchColumn();
    }
}
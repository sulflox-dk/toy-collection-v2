<?php
namespace App\Modules\Media\Models;

use App\Kernel\Database\BaseModel;

class MediaFile extends BaseModel
{
    protected static string $table = 'media_files';

    public static function getPaginated(int $page = 1, int $perPage = 20, string $search = '', string $attachmentType = '', string $tagId = ''): array
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

        // 3. NEW: Tag Filter
        if ($tagId !== '') {
            $where[] = "EXISTS (SELECT 1 FROM media_file_tags WHERE media_file_id = " . static::$table . ".id AND media_tag_id = ?)";
            $params[] = (int)$tagId;
        }

        // 4. Apply Conditions
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY created_at DESC";

        // 5. Count total
        $countSql = "SELECT COUNT(*) FROM ($sql) as sub";
        $total = (int) static::db()->query($countSql, $params)->fetchColumn();
        $totalPages = ceil($total / $perPage);

        // 6. Fetch items
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $items = static::db()->query($sql, $params)->fetchAll(\PDO::FETCH_ASSOC);

        // 7. NEW: Fetch assigned Tag IDs for each file
        foreach ($items as &$item) {
            $item['tag_ids'] = MediaTag::getIdsByFile($item['id']);
        }

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

    /**
     * Migrates polymorphic links from one media file to another.
     * Uses INSERT IGNORE to prevent duplicate key constraint violations 
     * if the new file is already attached to the target entity.
     */
    public static function migrateLinks(int $oldId, int $newId): void
    {
        $db = static::db();
        
        // 1. Recreate the links pointing to the new file
        $sqlInsert = "INSERT IGNORE INTO media_links (media_file_id, entity_id, entity_type, is_featured, sort_order)
                      SELECT ?, entity_id, entity_type, is_featured, sort_order FROM media_links WHERE media_file_id = ?";
        $db->query($sqlInsert, [$newId, $oldId]);
        
        // 2. Delete the old links
        $sqlDelete = "DELETE FROM media_links WHERE media_file_id = ?";
        $db->query($sqlDelete, [$oldId]);
    }

    /**
     * Search for media files by title or filename (for the autocomplete widget)
     */
    public static function searchSimple(string $query, int $limit = 30): array
    {
        $q = '%' . $query . '%';
        $sql = "SELECT id, filename, filepath, title FROM " . static::$table . "
                WHERE filename LIKE ? OR title LIKE ?
                ORDER BY created_at DESC LIMIT ?";

        return static::db()->query($sql, [$q, $q, $limit])->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Link an existing media file to any polymorphic entity
     */
    public static function linkToEntity(int $mediaFileId, string $entityType, int $entityId): void
    {
        // INSERT IGNORE prevents duplicate links if the user clicks it twice
        $sql = "INSERT IGNORE INTO media_links (media_file_id, entity_type, entity_id) VALUES (?, ?, ?)";
        static::db()->query($sql, [$mediaFileId, $entityType, $entityId]);
    }

    /**
     * Get all media files linked to a specific entity
     */
    public static function getForEntity(string $entityType, int $entityId): array
    {
        // Select all fields from f.* instead of just filepath/filename
        $sql = "SELECT ml.id as link_id, f.*, f.id as media_file_id 
                FROM media_links ml
                JOIN media_files f ON ml.media_file_id = f.id
                WHERE ml.entity_type = ? AND ml.entity_id = ?
                ORDER BY ml.is_featured DESC, ml.sort_order ASC";
                
        $items = static::db()->query($sql, [$entityType, $entityId])->fetchAll(\PDO::FETCH_ASSOC);
        
        // Append the tag IDs to each file
        foreach ($items as &$item) {
            $item['tag_ids'] = MediaTag::getIdsByFile($item['media_file_id']);
        }
        
        return $items;
    }
}
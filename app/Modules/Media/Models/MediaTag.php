<?php
namespace App\Modules\Media\Models;

use App\Kernel\Database\BaseModel;
use App\Kernel\Database\HasSlug;

class MediaTag extends BaseModel
{
    use HasSlug;
    protected static string $table = 'media_tags';

    public static function getPaginatedWithUsageCount(int $page = 1, int $perPage = 20, string $search = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $whereConditions = [];

        // Vi joiner for at tælle hvor mange gange taget bruges i media_file_tags
        $sql = "
            SELECT 
                t.*, 
                COUNT(l.media_file_id) as usage_count 
            FROM " . static::$table . " t
            LEFT JOIN media_file_tags l ON t.id = l.media_tag_id
        ";

        if ($search !== '') {
            $whereConditions[] = "t.name LIKE ?";
            $params[] = "%$search%";
        }

        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }

        $sql .= " GROUP BY t.id ORDER BY t.name ASC";

        // 1. Total Count 
        $countSql = "SELECT COUNT(*) FROM ($sql) as sub";
        $total = (int) static::db()->query($countSql, $params)->fetchColumn();
        $totalPages = ceil($total / $perPage);

        // 2. Limit & Offset
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
     * Henter et array af tag IDs tilknyttet en bestemt mediefil
     */
    public static function getIdsByFile(int $mediaFileId): array
    {
        $sql = "SELECT media_tag_id FROM media_file_tags WHERE media_file_id = ?";
        // fetchColumn(0) returnerer kun den første kolonne i resultatet som et fladt array [1, 5, 12]
        return static::db()->query($sql, [$mediaFileId])->fetchAll(\PDO::FETCH_COLUMN, 0);
    }

    /**
     * Synkroniserer tags for en fil (Sletter gamle, indsætter nye)
     */
    public static function syncForFile(int $mediaFileId, array $tagIds): void
    {
        // Start med at rydde de gamle tags for filen
        static::db()->query("DELETE FROM media_file_tags WHERE media_file_id = ?", [$mediaFileId]);
        
        // Hvis der ikke er valgt nogen nye tags, stopper vi bare her
        if (empty($tagIds)) {
            return;
        }

        // Ellers indsætter vi de nye tags ét ad gangen
        $sql = "INSERT INTO media_file_tags (media_file_id, media_tag_id) VALUES (?, ?)";
        foreach ($tagIds as $tagId) {
            static::db()->query($sql, [$mediaFileId, (int)$tagId]);
        }
    }

    /**
     * Henter alle tags sorteret alfabetisk
     */
    public static function getAll(): array
    {
        $sql = "SELECT * FROM " . static::$table . " ORDER BY name ASC";
        return static::db()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }
}
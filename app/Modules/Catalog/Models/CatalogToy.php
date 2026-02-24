<?php
namespace App\Modules\Catalog\Models;

use App\Kernel\Database\BaseModel;
use App\Kernel\Database\HasSlug;
use App\Kernel\Core\Config;
use App\Modules\Meta\Traits\HasManufacturer;
use App\Modules\Meta\Traits\HasUniverse;
use App\Modules\Meta\Traits\HasToyLine;
use App\Modules\Meta\Traits\HasEntertainmentSource;
use App\Modules\Meta\Traits\HasProductType;

class CatalogToy extends BaseModel
{
    use HasManufacturer, HasUniverse, HasToyLine, HasEntertainmentSource, HasProductType, HasSlug;

    protected static string $table = 'catalog_toys';

    public static function getPaginatedWithDetails(
        int $page = 1, 
        int $perPage = 20, 
        string $search = '', 
        int $universeId = 0,
        int $toyLineId = 0,
        int $manufacturerId = 0,
        int $productTypeId = 0,
        string $ownership = '',
        string $imageStatus = ''
    ): array {
        $offset = ($page - 1) * $perPage;
        $whereConditions = [];
        
        // 1. Fetch base URL and place it as the FIRST parameter for the PDO binding
        $baseUrl = rtrim(Config::get('app.url', ''), '/') . '/';
        $params = [$baseUrl]; 

        // 2. Core query using CONCAT(?) for the dynamic base URL
        $sql = "
            SELECT 
                t.*,
                u.name as universe_name,
                tl.name as toy_line_name,
                m.name as manufacturer_name,
                pt.name as product_type_name,
                es.name as entertainment_source_name,
                (SELECT COUNT(*) FROM catalog_toy_items WHERE catalog_toy_id = t.id) as item_count,
                (SELECT COUNT(*) FROM collection_toys WHERE catalog_toy_id = t.id AND deleted_at IS NULL) as collection_count,
                (SELECT CONCAT(?, f.filepath) 
                 FROM media_links ml 
                 JOIN media_files f ON ml.media_file_id = f.id 
                 WHERE ml.entity_type = 'catalog_toys' AND ml.entity_id = t.id 
                 ORDER BY ml.is_featured DESC, ml.sort_order ASC 
                 LIMIT 1) as image_path
            FROM " . static::$table . " t
            LEFT JOIN meta_universes u ON t.universe_id = u.id
            LEFT JOIN meta_toy_lines tl ON t.toy_line_id = tl.id
            LEFT JOIN meta_manufacturers m ON t.manufacturer_id = m.id
            LEFT JOIN meta_product_types pt ON t.product_type_id = pt.id
            LEFT JOIN meta_entertainment_sources es ON t.entertainment_source_id = es.id
        ";

        // 3. Apply Standard Filters (these parameters will be added after $baseUrl)
        if ($search !== '') {
            $whereConditions[] = "(t.name LIKE ? OR t.upc LIKE ? OR t.assortment_sku LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($universeId > 0) {
            $whereConditions[] = "t.universe_id = ?";
            $params[] = $universeId;
        }
        if ($toyLineId > 0) {
            $whereConditions[] = "t.toy_line_id = ?";
            $params[] = $toyLineId;
        }
        if ($manufacturerId > 0) {
            $whereConditions[] = "t.manufacturer_id = ?";
            $params[] = $manufacturerId;
        }
        if ($productTypeId > 0) {
            $whereConditions[] = "t.product_type_id = ?";
            $params[] = $productTypeId;
        }

        // 4. Apply Ownership Filter
        if ($ownership === 'owned') {
            $whereConditions[] = "EXISTS (SELECT 1 FROM collection_toys WHERE catalog_toy_id = t.id AND deleted_at IS NULL)";
        } elseif ($ownership === 'not_owned') {
            $whereConditions[] = "NOT EXISTS (SELECT 1 FROM collection_toys WHERE catalog_toy_id = t.id AND deleted_at IS NULL)";
        }

        // 5. Apply Image Filter
        if ($imageStatus === 'has_image') {
            $whereConditions[] = "EXISTS (SELECT 1 FROM media_links WHERE entity_type = 'catalog_toys' AND entity_id = t.id)";
        } elseif ($imageStatus === 'missing_image') {
            $whereConditions[] = "NOT EXISTS (SELECT 1 FROM media_links WHERE entity_type = 'catalog_toys' AND entity_id = t.id)";
        }

        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }

        $sql .= " ORDER BY t.name ASC";

        // 6. Total Count
        $countSql = "SELECT COUNT(*) FROM ($sql) as sub";
        $total = (int) static::db()->query($countSql, $params)->fetchColumn();
        $totalPages = ceil($total / $perPage);

        // 7. Pagination
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $items = static::db()->query($sql, $params)->fetchAll(\PDO::FETCH_ASSOC);

        // Look, no PHP foreach loop needed!
        return [
            'items' => $items,
            'total' => $total,
            'totalPages' => $totalPages
        ];
    }
}
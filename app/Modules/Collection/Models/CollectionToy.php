<?php
namespace App\Modules\Collection\Models;

use App\Kernel\Database\BaseModel;
use App\Kernel\Database\HasSlug;
use App\Kernel\Core\Config;
use App\Modules\Meta\Traits\HasAcquisitionStatus;
use App\Modules\Meta\Traits\HasPackagingType;
use App\Modules\Meta\Traits\HasConditionGrade;
use App\Modules\Meta\Traits\HasGraderTier;
use App\Modules\Meta\Traits\HasGradingCompany;


class CollectionToy extends BaseModel
{
    use HasAcquisitionStatus, HasPackagingType, HasConditionGrade, HasGraderTier, HasGradingCompany, HasSlug;

    protected static string $table = 'collection_toys';

    public static function getPaginatedWithDetails(
        int $page = 1, 
        int $perPage = 20, 
        string $search = '', 
        int $universeId = 0,
        int $toyLineId = 0,
        int $acquisitionStatusId = 0,
        int $storageUnitId = 0,
        int $manufacturerId = 0, // <-- NEW
        int $productTypeId = 0,  // <-- NEW
        string $missingParts = '',// <-- NEW
        string $imageStatus = ''  // <-- NEW
    ): array {
        $offset = ($page - 1) * $perPage;
        
        $baseUrl = rtrim(Config::get('app.url', ''), '/') . '/';
        $params = [$baseUrl, $baseUrl]; 
        $whereConditions = ["ct.deleted_at IS NULL"];

        $sql = "
            SELECT 
                ct.*,
                cat.name as toy_name,
                cat.year_released,
                u.name as universe_name,
                tl.name as toy_line_name,
                acq.name as acquisition_status,
                cond.name as condition_name,
                pack.name as packaging_name,
                su.name as storage_unit_name,
                su.box_code as storage_box_code,
                (SELECT COUNT(*) FROM collection_toy_items WHERE collection_toy_id = ct.id AND is_present = 1) as items_owned_count,
                (SELECT COUNT(*) FROM catalog_toy_items WHERE catalog_toy_id = cat.id) as items_total_count,
                
                COALESCE(
                    (SELECT CONCAT(?, f.filepath) FROM media_links ml JOIN media_files f ON ml.media_file_id = f.id WHERE ml.entity_type = 'collection_toys' AND ml.entity_id = ct.id ORDER BY ml.is_featured DESC, ml.sort_order ASC LIMIT 1),
                    (SELECT CONCAT(?, f.filepath) FROM media_links ml JOIN media_files f ON ml.media_file_id = f.id WHERE ml.entity_type = 'catalog_toys' AND ml.entity_id = cat.id ORDER BY ml.is_featured DESC, ml.sort_order ASC LIMIT 1)
                ) as image_path
                
            FROM " . static::$table . " ct
            JOIN catalog_toys cat ON ct.catalog_toy_id = cat.id
            LEFT JOIN meta_universes u ON cat.universe_id = u.id
            LEFT JOIN meta_toy_lines tl ON cat.toy_line_id = tl.id
            LEFT JOIN meta_acquisition_statuses acq ON ct.acquisition_status_id = acq.id
            LEFT JOIN meta_condition_grades cond ON ct.condition_grade_id = cond.id
            LEFT JOIN meta_packaging_types pack ON ct.packaging_type_id = pack.id
            LEFT JOIN collection_storage_units su ON ct.storage_unit_id = su.id
        ";

        // Existing Filters...
        if ($search !== '') {
            $whereConditions[] = "(cat.name LIKE ? OR su.box_code LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($universeId > 0) {
            $whereConditions[] = "cat.universe_id = ?";
            $params[] = $universeId;
        }
        if ($toyLineId > 0) {
            $whereConditions[] = "cat.toy_line_id = ?";
            $params[] = $toyLineId;
        }
        if ($acquisitionStatusId > 0) {
            $whereConditions[] = "ct.acquisition_status_id = ?";
            $params[] = $acquisitionStatusId;
        }
        if ($storageUnitId > 0) {
            $whereConditions[] = "ct.storage_unit_id = ?";
            $params[] = $storageUnitId;
        }

        // --- NEW FILTERS START HERE ---
        if ($manufacturerId > 0) {
            $whereConditions[] = "cat.manufacturer_id = ?";
            $params[] = $manufacturerId;
        }
        if ($productTypeId > 0) {
            $whereConditions[] = "cat.product_type_id = ?";
            $params[] = $productTypeId;
        }

        if ($missingParts === 'complete') {
            // Number of required catalog items equals number of present collection items
            $whereConditions[] = "(SELECT COUNT(*) FROM catalog_toy_items WHERE catalog_toy_id = cat.id) <= (SELECT COUNT(*) FROM collection_toy_items WHERE collection_toy_id = ct.id AND is_present = 1)";
        } elseif ($missingParts === 'missing') {
            // Number of required catalog items is greater than present collection items
            $whereConditions[] = "(SELECT COUNT(*) FROM catalog_toy_items WHERE catalog_toy_id = cat.id) > (SELECT COUNT(*) FROM collection_toy_items WHERE collection_toy_id = ct.id AND is_present = 1)";
        }

        if ($imageStatus === 'has_image') {
            // User uploaded their own photo for this collection item
            $whereConditions[] = "EXISTS (SELECT 1 FROM media_links WHERE entity_type = 'collection_toys' AND entity_id = ct.id)";
        } elseif ($imageStatus === 'missing_image') {
            // User has NOT uploaded their own photo (it may still show a catalog fallback in the UI)
            $whereConditions[] = "NOT EXISTS (SELECT 1 FROM media_links WHERE entity_type = 'collection_toys' AND entity_id = ct.id)";
        }
        // --- NEW FILTERS END HERE ---

        $sql .= " WHERE " . implode(" AND ", $whereConditions);
        $sql .= " ORDER BY ct.created_at DESC"; 

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
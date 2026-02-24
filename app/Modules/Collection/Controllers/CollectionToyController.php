<?php
namespace App\Modules\Collection\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Kernel\Database\Database;
use App\Kernel\Core\Config;
use App\Modules\Collection\Models\CollectionToy;
use App\Modules\Media\Models\MediaFile;
use App\Modules\Media\Models\MediaTag;

class CollectionToyController extends Controller
{
    public function index(Request $request): void
    {
        $db = Database::getInstance();

        $universes = $db->query("SELECT id, name FROM meta_universes ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $toyLines = $db->query("SELECT id, name FROM meta_toy_lines ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $statuses = $db->query("SELECT id, name FROM meta_acquisition_statuses ORDER BY sort_order ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $storageUnits = $db->query("SELECT id, name, box_code FROM collection_storage_units ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $manufacturers = $db->query("SELECT id, name FROM meta_manufacturers ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $productTypes = $db->query("SELECT id, name FROM meta_product_types ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);

        $this->render('collection_toy_index', [
            'title' => 'My Collection',
            'universes' => $universes,
            'toyLines' => $toyLines,
            'statuses' => $statuses,
            'storageUnits' => $storageUnits,
            'manufacturers' => $manufacturers,
            'productTypes' => $productTypes,
            'scripts' => [
                'assets/js/modules/collection/collection_toys.js'
            ]
        ]);
    }

    public function list(Request $request): void
    {
        $page = (int) $request->input('page', 1);

        $search = trim($request->input('q', ''));
        $universeId = (int) $request->input('universe_id', 0);
        $toyLineId = (int) $request->input('toy_line_id', 0);
        $acquisitionStatusId = (int) $request->input('acquisition_status_id', 0);
        $storageUnitId = (int) $request->input('storage_unit_id', 0);
        $manufacturerId = (int) $request->input('manufacturer_id', 0);
        $productTypeId = (int) $request->input('product_type_id', 0);
        $missingParts = trim($request->input('missing_parts', ''));
        $imageStatus = trim($request->input('image_status', ''));

        $viewMode = trim($request->input('view', ''));
        if ($viewMode === '') {
            $viewMode = $_COOKIE['collection-toy_view'] ?? 'cards';
        }
        if (!in_array($viewMode, ['list', 'cards'])) {
            $viewMode = 'cards';
        }

        $perPage = ($viewMode === 'cards') ? 24 : 20;

        $data = CollectionToy::getPaginatedWithDetails(
            $page, $perPage, $search,
            $universeId, $toyLineId, $acquisitionStatusId, $storageUnitId,
            $manufacturerId, $productTypeId, $missingParts, $imageStatus
        );

        $this->renderPartial('collection_toy_list', [
            'collectionToys' => $data['items'],
            'viewMode' => $viewMode,
            'pagination' => [
                'current' => $page,
                'total'   => $data['totalPages'],
                'count'   => $data['total']
            ]
        ]);
    }

    /**
     * WIZARD STEP 1: Search and pick a catalog toy
     */
    public function createStep1(Request $request): void
    {
        $this->renderPartial('collection_toy_step1');
    }

    /**
     * AJAX: Search catalog toys for the picker (returns JSON)
     */
    public function searchCatalog(Request $request): void
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 2) {
            $this->json([]);
            return;
        }

        $db = Database::getInstance();
        $baseUrl = rtrim(Config::get('app.url', ''), '/') . '/';

        $results = $db->query("
            SELECT
                cat.id,
                cat.name,
                cat.year_released,
                u.name as universe_name,
                tl.name as toy_line_name,
                m.name as manufacturer_name,
                pt.name as product_type_name,
                (SELECT CONCAT(?, f.filepath)
                 FROM media_links ml
                 JOIN media_files f ON ml.media_file_id = f.id
                 WHERE ml.entity_type = 'catalog_toys' AND ml.entity_id = cat.id
                 ORDER BY ml.is_featured DESC, ml.sort_order ASC LIMIT 1
                ) as image_path
            FROM catalog_toys cat
            LEFT JOIN meta_universes u ON cat.universe_id = u.id
            LEFT JOIN meta_toy_lines tl ON cat.toy_line_id = tl.id
            LEFT JOIN meta_manufacturers m ON cat.manufacturer_id = m.id
            LEFT JOIN meta_product_types pt ON cat.product_type_id = pt.id
            WHERE cat.deleted_at IS NULL
              AND cat.name LIKE ?
            ORDER BY cat.name ASC
            LIMIT 20
        ", [$baseUrl, "%$q%"])->fetchAll(\PDO::FETCH_ASSOC);

        $this->json($results);
    }

    /**
     * WIZARD STEP 2: Collection Details Form
     */
    public function createStep2(Request $request): void
    {
        $catalogToyId = (int) $request->input('catalog_toy_id', 0);
        $id = (int) $request->input('id', 0);
        $db = Database::getInstance();

        $collectionToy = null;
        $collectionItems = [];

        if ($id > 0) {
            $collectionToy = $db->query(
                "SELECT * FROM collection_toys WHERE id = ? AND deleted_at IS NULL", [$id]
            )->fetch(\PDO::FETCH_ASSOC);

            if ($collectionToy) {
                $catalogToyId = $collectionToy['catalog_toy_id'];
                $collectionItems = $db->query(
                    "SELECT * FROM collection_toy_items WHERE collection_toy_id = ? ORDER BY id ASC", [$id]
                )->fetchAll(\PDO::FETCH_ASSOC);
            }
        }

        if (!$catalogToyId) {
            echo '<div class="p-4 text-danger">Error: No catalog toy specified.</div>';
            return;
        }

        // Fetch the catalog toy details
        $baseUrl = rtrim(Config::get('app.url', ''), '/') . '/';
        $catalogToy = $db->query("
            SELECT cat.*,
                   u.name as universe_name,
                   tl.name as toy_line_name,
                   m.name as manufacturer_name,
                   pt.name as product_type_name,
                   (SELECT CONCAT(?, f.filepath) FROM media_links ml JOIN media_files f ON ml.media_file_id = f.id
                    WHERE ml.entity_type = 'catalog_toys' AND ml.entity_id = cat.id
                    ORDER BY ml.is_featured DESC, ml.sort_order ASC LIMIT 1) as image_path
            FROM catalog_toys cat
            LEFT JOIN meta_universes u ON cat.universe_id = u.id
            LEFT JOIN meta_toy_lines tl ON cat.toy_line_id = tl.id
            LEFT JOIN meta_manufacturers m ON cat.manufacturer_id = m.id
            LEFT JOIN meta_product_types pt ON cat.product_type_id = pt.id
            WHERE cat.id = ?
        ", [$baseUrl, $catalogToyId])->fetch(\PDO::FETCH_ASSOC);

        if (!$catalogToy) {
            echo '<div class="p-4 text-danger">Error: Catalog toy not found.</div>';
            return;
        }

        // Fetch catalog toy items (the "blueprint" of included parts)
        $catalogItems = $db->query("
            SELECT i.id, i.description, s.name as subject_name, s.type as subject_type
            FROM catalog_toy_items i
            JOIN meta_subjects s ON i.subject_id = s.id
            WHERE i.catalog_toy_id = ?
            ORDER BY i.id ASC
        ", [$catalogToyId])->fetchAll(\PDO::FETCH_ASSOC);

        // Fetch all lookups
        $acquisitionStatuses = $db->query("SELECT id, name FROM meta_acquisition_statuses ORDER BY sort_order ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $packagingTypes = $db->query("SELECT id, name FROM meta_packaging_types ORDER BY sort_order ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $conditionGrades = $db->query("SELECT id, name, abbreviation FROM meta_condition_grades ORDER BY sort_order ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $storageUnits = $db->query("SELECT id, name, box_code FROM collection_storage_units ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $gradingCompanies = $db->query("SELECT id, name FROM meta_grading_companies ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $graderTiers = $db->query("SELECT id, name FROM meta_grader_tiers ORDER BY sort_order ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $sources = $db->query("SELECT id, name FROM collection_sources ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);

        $this->renderPartial('collection_toy_step2', [
            'catalogToy' => $catalogToy,
            'catalogItems' => $catalogItems,
            'collectionToy' => $collectionToy,
            'collectionItems' => $collectionItems,
            'acquisitionStatuses' => $acquisitionStatuses,
            'packagingTypes' => $packagingTypes,
            'conditionGrades' => $conditionGrades,
            'storageUnits' => $storageUnits,
            'gradingCompanies' => $gradingCompanies,
            'graderTiers' => $graderTiers,
            'sources' => $sources,
        ]);
    }

    /**
     * WIZARD: Store / Update collection toy + items
     */
    public function store(Request $request): void
    {
        $db = Database::getInstance();

        $id = (int) $request->input('id', 0);
        $catalogToyId = (int) $request->input('catalog_toy_id', 0);

        $toyData = [
            'catalog_toy_id' => $catalogToyId,
            'storage_unit_id' => (int) $request->input('storage_unit_id', 0) ?: null,
            'purchase_source_id' => (int) $request->input('purchase_source_id', 0) ?: null,
            'acquisition_status_id' => (int) $request->input('acquisition_status_id', 0) ?: null,
            'date_acquired' => $request->input('date_acquired', '') ?: null,
            'purchase_price' => $request->input('purchase_price', '') !== '' ? (float) $request->input('purchase_price') : null,
            'purchase_currency' => trim($request->input('purchase_currency', 'USD')) ?: 'USD',
            'current_value' => $request->input('current_value', '') !== '' ? (float) $request->input('current_value') : null,
            'packaging_type_id' => (int) $request->input('packaging_type_id', 0) ?: null,
            'condition_grade_id' => (int) $request->input('condition_grade_id', 0) ?: null,
            'grader_company_id' => (int) $request->input('grader_company_id', 0) ?: null,
            'grader_tier_id' => (int) $request->input('grader_tier_id', 0) ?: null,
            'grade_serial' => trim($request->input('grade_serial', '')) ?: null,
            'grade_score' => trim($request->input('grade_score', '')) ?: null,
            'notes' => trim($request->input('notes', '')) ?: null,
        ];

        $items = $request->input('items', []);

        try {
            $db->beginTransaction();

            if ($id > 0) {
                // Update — don't change catalog_toy_id
                unset($toyData['catalog_toy_id']);
                CollectionToy::update($id, $toyData);
            } else {
                $id = CollectionToy::create($toyData);
            }

            // Sync items
            $existingItemIds = $db->query(
                "SELECT id FROM collection_toy_items WHERE collection_toy_id = ?", [$id]
            )->fetchAll(\PDO::FETCH_COLUMN);

            $keptItemIds = [];

            foreach ($items as $item) {
                $itemId = (int) ($item['id'] ?? 0);
                $catalogToyItemId = (int) ($item['catalog_toy_item_id'] ?? 0);
                if (!$catalogToyItemId) continue;

                $itemData = [
                    'collection_toy_id' => $id,
                    'catalog_toy_item_id' => $catalogToyItemId,
                    'is_present' => !empty($item['is_present']) ? 1 : 0,
                    'is_repro' => !empty($item['is_repro']) ? 1 : 0,
                    'acquisition_status_id' => (int) ($item['acquisition_status_id'] ?? 0) ?: null,
                    'packaging_type_id' => (int) ($item['packaging_type_id'] ?? 0) ?: null,
                    'condition_grade_id' => (int) ($item['condition_grade_id'] ?? 0) ?: null,
                    'storage_unit_id' => (int) ($item['storage_unit_id'] ?? 0) ?: null,
                    'condition_notes' => trim($item['condition_notes'] ?? '') ?: null,
                ];

                if ($itemId > 0 && in_array($itemId, $existingItemIds)) {
                    unset($itemData['collection_toy_id']);
                    $sets = [];
                    $params = [];
                    foreach ($itemData as $col => $val) {
                        $sets[] = "$col = ?";
                        $params[] = $val;
                    }
                    $params[] = $itemId;
                    $db->query("UPDATE collection_toy_items SET " . implode(', ', $sets) . " WHERE id = ?", $params);
                    $keptItemIds[] = $itemId;
                } else {
                    $cols = array_keys($itemData);
                    $placeholders = array_fill(0, count($cols), '?');
                    $db->query(
                        "INSERT INTO collection_toy_items (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")",
                        array_values($itemData)
                    );
                    $keptItemIds[] = $db->lastInsertId();
                }
            }

            $itemsToDelete = array_diff($existingItemIds, $keptItemIds);
            if (!empty($itemsToDelete)) {
                $placeholders = implode(',', array_fill(0, count($itemsToDelete), '?'));
                $db->query("DELETE FROM collection_toy_items WHERE id IN ($placeholders)", array_values($itemsToDelete));
            }

            $db->commit();

            $this->json(['success' => true, 'id' => $id]);

        } catch (\PDOException $e) {
            $db->rollBack();
            $this->json(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    /**
     * WIZARD STEP 3: Media Manager
     */
    public function createStep3(Request $request): void
    {
        $id = (int) $request->input('id', 0);
        if (!$id) {
            echo '<div class="p-4 text-danger">Error: Missing Collection Toy ID.</div>';
            return;
        }

        $db = Database::getInstance();

        $collectionToy = $db->query("
            SELECT ct.id, cat.name as toy_name
            FROM collection_toys ct
            JOIN catalog_toys cat ON ct.catalog_toy_id = cat.id
            WHERE ct.id = ? AND ct.deleted_at IS NULL
        ", [$id])->fetch(\PDO::FETCH_ASSOC);

        if (!$collectionToy) {
            echo '<div class="p-4 text-danger">Error: Collection toy not found.</div>';
            return;
        }

        // Only show items marked as present
        $items = $db->query("
            SELECT ci.id, s.name as subject_name, s.type as subject_type, cti.description
            FROM collection_toy_items ci
            JOIN catalog_toy_items cti ON ci.catalog_toy_item_id = cti.id
            JOIN meta_subjects s ON cti.subject_id = s.id
            WHERE ci.collection_toy_id = ? AND ci.is_present = 1
            ORDER BY ci.id ASC
        ", [$id])->fetchAll(\PDO::FETCH_ASSOC);

        $availableTags = MediaTag::getAll();

        $this->renderPartial('collection_toy_step3', [
            'collectionToy' => $collectionToy,
            'items' => $items,
            'availableTags' => $availableTags
        ]);
    }

    /**
     * Delete a collection toy (soft delete)
     */
    public function destroy(Request $request, int $id): void
    {
        $db = Database::getInstance();

        $toy = $db->query(
            "SELECT id FROM collection_toys WHERE id = ? AND deleted_at IS NULL", [$id]
        )->fetch(\PDO::FETCH_ASSOC);

        if (!$toy) {
            $this->json(['error' => 'Collection toy not found'], 404);
            return;
        }

        try {
            $db->beginTransaction();

            // Remove media links for the toy and its items
            $db->query("DELETE FROM media_links WHERE entity_type = 'collection_toys' AND entity_id = ?", [$id]);

            $itemIds = $db->query(
                "SELECT id FROM collection_toy_items WHERE collection_toy_id = ?", [$id]
            )->fetchAll(\PDO::FETCH_COLUMN);

            if (!empty($itemIds)) {
                $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
                $db->query(
                    "DELETE FROM media_links WHERE entity_type = 'collection_toy_items' AND entity_id IN ($placeholders)",
                    $itemIds
                );
            }

            // Soft delete
            $db->query("UPDATE collection_toys SET deleted_at = NOW() WHERE id = ?", [$id]);

            $db->commit();
            $this->json(['success' => true]);
        } catch (\PDOException $e) {
            $db->rollBack();
            $this->json(['error' => 'Failed to delete. ' . $e->getMessage()], 500);
        }
    }
}

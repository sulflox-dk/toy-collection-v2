<?php
namespace App\Modules\Catalog\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Kernel\Database\Database;
use App\Kernel\Core\Config;
use App\Modules\Catalog\Models\CatalogToy;
use App\Modules\Media\Models\MediaFile;
use App\Modules\Media\Models\MediaTag;

class CatalogToyController extends Controller
{
    /**
     * meta_subjects.type values that make sense as a catalog toy's own
     * "main" subject — never 'Accessory'/'Packaging'/'Paperwork', which
     * describe what comes WITH a toy, not what the toy itself depicts.
     */
    private const MAIN_SUBJECT_TYPES = ['Character', 'Vehicle', 'Environment', 'Creature'];

    public function index(Request $request): void
    {
        $db = Database::getInstance();
        
        // Fetch lookup data for filters
        $universes = $db->query("SELECT id, name FROM meta_universes ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $toyLines = $db->query("SELECT id, name FROM meta_toy_lines ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $manufacturers = $db->query("SELECT id, name FROM meta_manufacturers ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $productTypes = $db->query("SELECT id, name FROM meta_product_types ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $subjects = $db->query("SELECT id, name FROM meta_subjects ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);

        $this->render('catalog_toy_index', [
            'title' => 'Catalog Toys',
            'universes' => $universes,
            'toyLines' => $toyLines,
            'manufacturers' => $manufacturers,
            'productTypes' => $productTypes,
            'subjects' => $subjects,
            'scripts' => [
                'assets/js/modules/catalog/catalog_toys.js'
            ]
        ]);
    }

    public function list(Request $request): void
    {
        $page = (int) $request->input('page', 1);
        
        // Retrieve standard filters
        $search = trim($request->input('q', ''));
        $universeId = (int) $request->input('universe_id', 0);
        $toyLineId = (int) $request->input('toy_line_id', 0);
        $manufacturerId = (int) $request->input('manufacturer_id', 0);
        $productTypeId = (int) $request->input('product_type_id', 0);
        $ownership = trim($request->input('ownership', ''));
        $imageStatus = trim($request->input('image_status', ''));
        $subjectId = (int) $request->input('subject_id', 0);
        
        // --- FIXED VIEW MODE LOGIC ---
        $viewMode = trim($request->input('view', '')); 
        
        if ($viewMode === '') {
            $viewMode = $_COOKIE['catalog-toy_view'] ?? 'list';
        }
        
        if (!in_array($viewMode, ['list', 'cards'])) {
            $viewMode = 'list';
        }

        $perPage = ($viewMode === 'cards') ? 24 : 20;

        $data = CatalogToy::getPaginatedWithDetails(
            $page, $perPage, $search,
            $universeId, $toyLineId, $manufacturerId, $productTypeId,
            $ownership, $imageStatus, $subjectId
        );

        $this->renderPartial('catalog_toy_list', [
            'catalogToys' => $data['items'],
            'viewMode' => $viewMode,
            'pagination' => [
                'current' => $page,
                'total'   => $data['totalPages'],
                'count'   => $data['total']
            ]
        ]);
    }

    /**
     * WIZARD STEP 1: Select Universe
     */
    public function createStep1(Request $request): void
    {
        $db = Database::getInstance();
        $universes = $db->query("
            SELECT id, name, slug,
                (SELECT f.filepath FROM media_links ml
                 JOIN media_files f ON ml.media_file_id = f.id
                 WHERE ml.entity_type = 'universes' AND ml.entity_id = meta_universes.id
                 ORDER BY ml.is_featured DESC, ml.sort_order ASC LIMIT 1) AS image_path
            FROM meta_universes ORDER BY name ASC
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $baseUrl = rtrim(Config::get('app.url', ''), '/') . '/';

        $this->renderPartial('catalog_toy_step1', [
            'universes' => $universes,
            'baseUrl' => $baseUrl
        ]);
    }

    /**
     * WIZARD STEP 2: Main Data Form & Items
     */
    public function createStep2(Request $request): void
    {
        $universeId = (int) $request->input('universe_id', 0);
        $id = (int) $request->input('id', 0);
        $db = Database::getInstance();

        // --- NEW: Fetch existing data if editing ---
        $toy = null;
        $items = [];
        $descriptions = [];
        $isEdit = false;

        if ($id > 0) {
            $toy = $db->query("
                SELECT ct.*, s.name AS subject_name, s.type AS subject_type,
                    (SELECT f.filepath FROM media_links ml
                     JOIN media_files f ON ml.media_file_id = f.id
                     WHERE ml.entity_type = 'catalog_toys' AND ml.entity_id = ct.id
                     ORDER BY ml.is_featured DESC, ml.sort_order ASC LIMIT 1) AS image_path
                FROM catalog_toys ct
                LEFT JOIN meta_subjects s ON ct.subject_id = s.id
                WHERE ct.id = ?
            ", [$id])->fetch(\PDO::FETCH_ASSOC);
            if ($toy) {
                $isEdit = true;
                $universeId = $toy['universe_id']; // Override with the saved universe

                // Fetch existing items, each with its own primary photo (if any)
                $items = $db->query("
                    SELECT i.*, s.name as subject_name, s.type as subject_type,
                        (SELECT f.filepath FROM media_links ml
                         JOIN media_files f ON ml.media_file_id = f.id
                         WHERE ml.entity_type = 'catalog_toy_items' AND ml.entity_id = i.id
                         ORDER BY ml.is_featured DESC, ml.sort_order ASC LIMIT 1) AS image_path
                    FROM catalog_toy_items i
                    LEFT JOIN meta_subjects s ON i.subject_id = s.id
                    WHERE i.catalog_toy_id = ?
                    ORDER BY i.id ASC
                ", [$id])->fetchAll(\PDO::FETCH_ASSOC);

                // Read-only reference: every attributed description collected
                // from import sources (never edited here — the toy's own
                // `description` field above is the collector's own words).
                $descriptions = $db->query("
                    SELECT description, source_name, source_url
                    FROM catalog_toy_descriptions
                    WHERE catalog_toy_id = ?
                    ORDER BY id ASC
                ", [$id])->fetchAll(\PDO::FETCH_ASSOC);
            }
        }
        // -------------------------------------------

        // 1. Fetch Lookups
        $universes = $db->query("SELECT id, name FROM meta_universes ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $productTypes = $db->query("SELECT id, name FROM meta_product_types ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
        
        // 2. Fetch Dependent Lookups
        $manufacturers = $db->query("SELECT id, name FROM meta_manufacturers ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $toyLines = $db->query("SELECT id, name, universe_id, manufacturer_id FROM meta_toy_lines ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $entertainmentSources = $db->query("SELECT id, name, type, universe_id FROM meta_entertainment_sources ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $subjects = $db->query("SELECT id, name, type, universe_id FROM meta_subjects ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);

        $baseUrl = rtrim(Config::get('app.url', ''), '/') . '/';

        $this->renderPartial('catalog_toy_step2', [
            'universeId' => $universeId,
            'universes' => $universes,
            'manufacturers' => $manufacturers,
            'toyLines' => $toyLines,
            'productTypes' => $productTypes,
            'entertainmentSources' => $entertainmentSources,
            'subjects' => $subjects,
            'mainSubjectTypes' => self::MAIN_SUBJECT_TYPES,
            'toy' => $toy,
            'items' => $items,
            'descriptions' => $descriptions,
            'isEdit' => $isEdit,
            'baseUrl' => $baseUrl
        ]);
    }

    /**
     * WIZARD: Store Step 2 Data (Main Toy + Items)
     */
    public function store(Request $request): void
    {
        $db = Database::getInstance();
        
        $id = (int) $request->input('id', 0);
        $universeId = (int) $request->input('universe_id', 0);
        $manufacturerId = (int) $request->input('manufacturer_id', 0) ?: null;
        $toyLineId = (int) $request->input('toy_line_id', 0) ?: null;
        $productTypeId = (int) $request->input('product_type_id', 0) ?: null;
        $entertainmentSourceId = (int) $request->input('entertainment_source_id', 0) ?: null;
        $subjectId = (int) $request->input('subject_id', 0) ?: null;

        $name = trim($request->input('name', ''));
        $yearReleased = (int) $request->input('year_released', 0) ?: null;
        $wave = trim($request->input('wave', ''));
        $assortmentSku = trim($request->input('assortment_sku', ''));
        $upc = trim($request->input('upc', ''));
        $description = trim($request->input('description', ''));

        $items = $request->input('items', []);

        try {
            $db->beginTransaction();

            if ($id > 0) {
                $sql = "UPDATE catalog_toys SET
                        universe_id = ?, manufacturer_id = ?, toy_line_id = ?, product_type_id = ?,
                        entertainment_source_id = ?, subject_id = ?, name = ?, year_released = ?, wave = ?,
                        assortment_sku = ?, upc = ?, description = ?
                        WHERE id = ?";
                $db->query($sql, [$universeId, $manufacturerId, $toyLineId, $productTypeId, $entertainmentSourceId, $subjectId, $name, $yearReleased, $wave, $assortmentSku, $upc, $description, $id]);
            } else {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-')) . '-' . time();
                $sql = "INSERT INTO catalog_toys
                        (universe_id, manufacturer_id, toy_line_id, product_type_id, entertainment_source_id, subject_id, name, slug, year_released, wave, assortment_sku, upc, description)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $db->query($sql, [$universeId, $manufacturerId, $toyLineId, $productTypeId, $entertainmentSourceId, $subjectId, $name, $slug, $yearReleased, $wave, $assortmentSku, $upc, $description]);
                $id = $db->lastInsertId();
            }

            $existingItemIds = [];
            if ($id > 0) {
                $stmt = $db->query("SELECT id FROM catalog_toy_items WHERE catalog_toy_id = ?", [$id]);
                $existingItemIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            }

            $keptItemIds = [];

            foreach ($items as $item) {
                $itemId = (int) ($item['id'] ?? 0);
                $subjectId = (int) ($item['subject_id'] ?? 0);
                $description = trim($item['description'] ?? '');

                if (!$subjectId) continue; 

                if ($itemId > 0 && in_array($itemId, $existingItemIds)) {
                    $db->query("UPDATE catalog_toy_items SET subject_id = ?, description = ? WHERE id = ?", [$subjectId, $description, $itemId]);
                    $keptItemIds[] = $itemId;
                } else {
                    $db->query("INSERT INTO catalog_toy_items (catalog_toy_id, subject_id, description) VALUES (?, ?, ?)", [$id, $subjectId, $description]);
                    $keptItemIds[] = $db->lastInsertId();
                }
            }

            $itemsToDelete = array_diff($existingItemIds, $keptItemIds);
            if (!empty($itemsToDelete)) {
                $placeholders = implode(',', array_fill(0, count($itemsToDelete), '?'));
                $db->query("DELETE FROM catalog_toy_items WHERE id IN ($placeholders)", array_values($itemsToDelete));
            }

            $db->commit();

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'id' => $id]);
            exit;

        } catch (\RuntimeException $e) {
            $db->rollBack();
            
            if ($e->getCode() == 23000 || strpos($e->getMessage(), 'foreign key constraint') !== false) {
                $msg = 'Cannot delete an item because it is currently linked to a user\'s collection.';
            } else {
                $msg = 'Database error: ' . $e->getMessage();
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $msg]);
            exit;
        }
    }

    /**
     * WIZARD STEP 3: Image Manager
     */
    public function createStep3(Request $request): void
    {
        $id = (int) $request->input('id', 0);
        if (!$id) {
            echo '<div class="p-4 text-danger">Error: Missing Toy ID.</div>';
            return;
        }

        $db = Database::getInstance();

        $toy = $db->query("SELECT id, name FROM catalog_toys WHERE id = ?", [$id])->fetch(\PDO::FETCH_ASSOC);
        if (!$toy) {
            echo '<div class="p-4 text-danger">Error: Toy not found.</div>';
            return;
        }

        $items = $db->query("
            SELECT i.id, i.description, s.name as subject_name, s.type as subject_type 
            FROM catalog_toy_items i
            JOIN meta_subjects s ON i.subject_id = s.id
            WHERE i.catalog_toy_id = ?
            ORDER BY i.id ASC
        ", [$id])->fetchAll(\PDO::FETCH_ASSOC);

        $toy['images'] = MediaFile::getForEntity('catalog_toys', $id);
        
        foreach ($items as &$item) {
            $item['images'] = MediaFile::getForEntity('catalog_toy_items', $item['id']);
        }

        // --- THE MISSING CODE: FETCH TAGS ---
        $availableTags = MediaTag::getAll();

        $this->renderPartial('catalog_toy_step3', [
            'toy' => $toy,
            'items' => $items,
            'availableTags' => $availableTags // <-- Pass them to the view
        ]);
    }

    /**
     * Delete a catalog toy and its items.
     */
    public function destroy(Request $request, int $id): void
    {
        $db = Database::getInstance();

        $toy = $db->query("SELECT id FROM catalog_toys WHERE id = ?", [$id])->fetch(\PDO::FETCH_ASSOC);
        if (!$toy) {
            $this->json(['error' => 'Catalog toy not found'], 404);
            return;
        }

        // Check if any collection entries reference this catalog toy
        $collectionCount = (int) $db->query(
            "SELECT COUNT(*) FROM collection_toys WHERE catalog_toy_id = ? AND deleted_at IS NULL",
            [$id]
        )->fetchColumn();

        if ($collectionCount > 0) {
            $this->json([
                'error' => "Cannot delete: this toy is referenced by {$collectionCount} collection item(s). Remove them first."
            ], 409);
            return;
        }

        try {
            $db->beginTransaction();

            // Remove media links for the toy and its items
            $itemIds = $db->query("SELECT id FROM catalog_toy_items WHERE catalog_toy_id = ?", [$id])
                ->fetchAll(\PDO::FETCH_COLUMN);

            $db->query("DELETE FROM media_links WHERE entity_type = 'catalog_toys' AND entity_id = ?", [$id]);

            if (!empty($itemIds)) {
                $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
                $db->query(
                    "DELETE FROM media_links WHERE entity_type = 'catalog_toy_items' AND entity_id IN ($placeholders)",
                    $itemIds
                );
            }

            // collection_toys.catalog_toy_id is a hard (ON DELETE RESTRICT)
            // reference. The guard above only blocks on ACTIVE collection
            // entries, so a soft-deleted one (invisible to that check, but
            // never actually removed from the table) would otherwise fail
            // this delete with a raw FK violation — safe to clear here
            // since it's already gone from the user's perspective, and
            // ON DELETE CASCADE on collection_toy_items takes its item
            // rows with it.
            $db->query(
                "DELETE FROM collection_toys WHERE catalog_toy_id = ? AND deleted_at IS NOT NULL",
                [$id]
            );

            // Delete items then toy (items have ON DELETE CASCADE, but explicit is clearer)
            $db->query("DELETE FROM catalog_toy_items WHERE catalog_toy_id = ?", [$id]);
            $db->query("DELETE FROM catalog_toys WHERE id = ?", [$id]);

            $db->commit();
            $this->json(['success' => true]);
        } catch (\RuntimeException $e) {
            $db->rollBack();
            $this->json(['error' => 'Failed to delete catalog toy. ' . $e->getMessage()], 500);
        }
    }
}
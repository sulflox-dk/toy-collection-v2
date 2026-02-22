<?php
namespace App\Modules\Collection\Controllers;

use App\Kernel\Database\Database;
use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Modules\Collection\Models\CollectionStorageUnit;

class CollectionStorageUnitController extends Controller
{
    public function index(Request $request): void
    {
        $this->render('storage_unit_index', [
            'title' => 'Storage Units'
        ]);
    }

    public function list(Request $request): void
    {
        $page = (int) $request->input('page', 1);
        $perPage = 20;
        $search = trim($request->input('q', ''));

        $data = CollectionStorageUnit::getPaginated($page, $perPage, $search);

        $this->renderPartial('storage_unit_list', [
            'storageUnits' => $data['items'],
            'pagination' => [
                'current' => $page,
                'total'   => $data['totalPages'],
                'count'   => $data['total']
            ]
        ]);
    }

    public function store(Request $request): void
    {
        $name = trim($request->input('name', ''));
        
        if ($name === '') {
            $this->json(['field' => 'name', 'message' => 'Name is required'], 422);
            return;
        }

        CollectionStorageUnit::create([
            'box_code' => trim($request->input('box_code', '')),
            'name' => $name,
            'location' => trim($request->input('location', '')),
            'description' => trim($request->input('description', ''))
        ]);

        $this->json(['success' => true]);
    }

    public function update(Request $request, int $id): void
    {
        if (!CollectionStorageUnit::find($id)) {
            $this->json(['error' => 'Record not found'], 404);
            return;
        }

        $name = trim($request->input('name', ''));

        if ($name === '') {
            $this->json(['field' => 'name', 'message' => 'Name is required'], 422);
            return;
        }

        CollectionStorageUnit::update($id, [
            'box_code' => trim($request->input('box_code', '')),
            'name' => $name,
            'location' => trim($request->input('location', '')),
            'description' => trim($request->input('description', ''))
        ]);

        $updated = CollectionStorageUnit::find($id);

        ob_start();
        $this->renderPartial('storage_unit_row', ['su' => $updated]);
        $this->json(['success' => true, 'row_html' => ob_get_clean()]);
    }

    public function destroy(Request $request, int $id): void
    {
        $db = Database::getInstance();
        
        // 1. Check Dependencies (collection_toys)
        $inUseCount = 0; 
        try {
            // We use a try-catch just in case the collection_toys table hasn't been created yet
            $inUseCount = (int) $db->query("SELECT COUNT(*) FROM collection_toys WHERE storage_unit_id = ?", [$id])->fetchColumn();
        } catch (\Exception $e) {
            // Table might not exist yet, ignore safely
        }

        // 2. Validate Migration Target
        $migrateTo = (int) $request->input('migrate_to', 0);

        if ($migrateTo > 0) {
            // Error 1: Trying to migrate to itself
            if ($migrateTo === $id) {
                $this->json(['error' => 'Cannot migrate items to the unit being deleted.'], 400);
                return;
            }
            
            // Error 2: Target does not exist
            if (!CollectionStorageUnit::find($migrateTo)) {
                $this->json(['error' => 'The selected destination unit does not exist.'], 400);
                return;
            }
        }

        // 3. Conflict Check (409) - Stop here if we have items but no migration target
        if ($inUseCount > 0 && $migrateTo === 0) {
            $this->json([
                'requires_migration' => true,
                'message' => "This unit currently contains {$inUseCount} item(s). Please move them to another storage unit before deleting.",
                'options_url' => "storage-unit/migrate-on-delete-options?exclude={$id}"
            ], 409);
            return;
        }

        // 4. Execute Deletion with Transaction
        try {
            $db->beginTransaction();

            // Migrate if requested
            if ($inUseCount > 0 && $migrateTo > 0) {
                $db->query("UPDATE collection_toys SET storage_unit_id = ? WHERE storage_unit_id = ?", [$migrateTo, $id]);
            }

            // Delete the storage unit
            CollectionStorageUnit::delete($id);

            $db->commit();
            $this->json(['success' => true]);

        } catch (\Exception $e) {
            $db->rollBack();
            error_log('Delete failed: ' . $e->getMessage());
            $this->json(['error' => 'Failed to delete record. Please try again.'], 500);
        }
    }

    /**
     * Returns a JSON list of storage units for the Migration Dropdown
     * GET /storage-unit/migrate-on-delete-options?exclude={id}
     */
    public function migrateOnDeleteOptions(Request $request): void
    {
        $exclude = (int) $request->input('exclude', 0);
        $db = Database::getInstance();
        
        $sql = "SELECT id, name, box_code FROM collection_storage_units";
        $params = [];
        
        if ($exclude > 0) {
            $sql .= " WHERE id != ?";
            $params[] = $exclude;
        }
        
        $sql .= " ORDER BY name ASC";
        
        $options = $db->query($sql, $params)->fetchAll(\PDO::FETCH_ASSOC);
        
        // Format the name nicely so the dropdown shows "Box Code - Name"
        $formattedOptions = array_map(function($opt) {
            $displayName = $opt['name'];
            if (!empty($opt['box_code'])) {
                $displayName = $opt['box_code'] . ' - ' . $displayName;
            }
            return [
                'id' => $opt['id'],
                'name' => $displayName
            ];
        }, $options);

        $this->json($formattedOptions);
    }
}
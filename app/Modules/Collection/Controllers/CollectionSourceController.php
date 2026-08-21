<?php
namespace App\Modules\Collection\Controllers;

use App\Kernel\Database\Database;
use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Modules\Collection\Models\CollectionSource;

class CollectionSourceController extends Controller
{
    public function index(Request $request): void
    {
        $this->render('collection_source_index', [
            'title' => 'Purchase Sources'
        ]);
    }

    public function list(Request $request): void
    {
        $page = (int) $request->input('page', 1);
        $perPage = 20;
        $search = trim($request->input('q', ''));

        $data = CollectionSource::getPaginated($page, $perPage, $search);

        $this->renderPartial('collection_source_list', [
            'sources' => $data['items'],
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

        CollectionSource::create([
            'name' => $name,
            'website' => trim($request->input('website', '')) ?: null
        ]);

        $this->json(['success' => true]);
    }

    public function update(Request $request, int $id): void
    {
        if (!CollectionSource::find($id)) {
            $this->json(['error' => 'Record not found'], 404);
            return;
        }

        $name = trim($request->input('name', ''));

        if ($name === '') {
            $this->json(['field' => 'name', 'message' => 'Name is required'], 422);
            return;
        }

        CollectionSource::update($id, [
            'name' => $name,
            'website' => trim($request->input('website', '')) ?: null
        ]);

        $updated = CollectionSource::find($id);

        ob_start();
        $this->renderPartial('collection_source_row', ['source' => $updated]);
        $this->json(['success' => true, 'row_html' => ob_get_clean()]);
    }

    public function destroy(Request $request, int $id): void
    {
        $db = Database::getInstance();

        // 1. Check Dependencies (collection_toys)
        $inUseCount = 0;
        try {
            $inUseCount = (int) $db->query("SELECT COUNT(*) FROM collection_toys WHERE purchase_source_id = ?", [$id])->fetchColumn();
        } catch (\Exception $e) {
            // Table might not exist yet, ignore safely
        }

        // 2. Validate Migration Target
        $migrateTo = (int) $request->input('migrate_to', 0);

        if ($migrateTo > 0) {
            if ($migrateTo === $id) {
                $this->json(['error' => 'Cannot migrate items to the source being deleted.'], 400);
                return;
            }

            if (!CollectionSource::find($migrateTo)) {
                $this->json(['error' => 'The selected destination source does not exist.'], 400);
                return;
            }
        }

        // 3. Conflict Check (409) - Stop here if we have items but no migration target
        if ($inUseCount > 0 && $migrateTo === 0) {
            $this->json([
                'requires_migration' => true,
                'message' => "This source is currently used by {$inUseCount} item(s). Please reassign them to another source before deleting.",
                'options_url' => "collection-source/migrate-on-delete-options?exclude={$id}"
            ], 409);
            return;
        }

        // 4. Execute Deletion with Transaction
        try {
            $db->beginTransaction();

            if ($inUseCount > 0 && $migrateTo > 0) {
                $db->query("UPDATE collection_toys SET purchase_source_id = ? WHERE purchase_source_id = ?", [$migrateTo, $id]);
            }

            CollectionSource::delete($id);

            $db->commit();
            $this->json(['success' => true]);

        } catch (\Exception $e) {
            $db->rollBack();
            error_log('Delete failed: ' . $e->getMessage());
            $this->json(['error' => 'Failed to delete record. Please try again.'], 500);
        }
    }

    /**
     * Returns a JSON list of sources for the Migration Dropdown
     * GET /collection-source/migrate-on-delete-options?exclude={id}
     */
    public function migrateOnDeleteOptions(Request $request): void
    {
        $exclude = (int) $request->input('exclude', 0);
        $db = Database::getInstance();

        $sql = "SELECT id, name FROM collection_sources";
        $params = [];

        if ($exclude > 0) {
            $sql .= " WHERE id != ?";
            $params[] = $exclude;
        }

        $sql .= " ORDER BY name ASC";

        $options = $db->query($sql, $params)->fetchAll(\PDO::FETCH_ASSOC);

        $this->json($options);
    }
}

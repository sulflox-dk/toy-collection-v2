<?php
namespace App\Modules\Meta\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Kernel\Database\Database;
use App\Modules\Meta\Models\AcquisitionStatus;

// Ensure these exist or create placeholders if you haven't built the Collection module yet
use App\Modules\Collection\Models\CollectionToy;
use App\Modules\Collection\Models\CollectionToyItem;

class AcquisitionStatusController extends Controller
{
    public function index(Request $request): void
    {
        $this->render('acquisition_status_index', ['title' => 'Acquisition Statuses']);
    }

    public function list(Request $request): void
    {
        $page = (int) $request->input('page', 1);
        $perPage = 20;
        $search = trim($request->input('q', ''));

        $data = AcquisitionStatus::getPaginated($page, $perPage, $search);

        $this->renderPartial('acquisition_status_list', [
            'statuses' => $data['items'],
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

        $slug = AcquisitionStatus::validateUniqueSlug($request->input('slug'), $name);
        if ($slug === null) {
            $this->json(['field' => 'slug', 'message' => 'Slug already exists'], 422);
            return;
        }

        AcquisitionStatus::create([
            'name' => $name,
            'slug' => $slug,
            'sort_order' => (int) $request->input('sort_order', 0)
        ]);

        $this->json(['success' => true]);
    }

    public function update(Request $request, int $id): void
    {
        if (!AcquisitionStatus::find($id)) {
            $this->json(['error' => 'Record not found'], 404);
            return;
        }

        $name = trim($request->input('name', ''));
        $slug = AcquisitionStatus::validateUniqueSlug($request->input('slug'), $name, $id);
        
        if ($slug === null) {
            $this->json(['field' => 'slug', 'message' => 'Slug already exists'], 422);
            return;
        }

        AcquisitionStatus::update($id, [
            'name' => $name,
            'slug' => $slug,
            'sort_order' => (int) $request->input('sort_order', 0)
        ]);

        $updated = AcquisitionStatus::find($id);
        ob_start();
        $this->renderPartial('acquisition_status_row', ['status' => $updated]);
        
        $this->json(['success' => true, 'row_html' => ob_get_clean()]);
    }

    public function destroy(Request $request, int $id): void
    {
        // 1. Check Dependencies (Collection Toys AND Items)
        $toyCount = 0;
        $itemCount = 0;

        if (class_exists(CollectionToy::class)) {
            $toyCount = CollectionToy::countByAcquisitionStatus($id);
        }
        if (class_exists(CollectionToyItem::class)) {
            $itemCount = CollectionToyItem::countByAcquisitionStatus($id);
        }

        $totalDependencies = $toyCount + $itemCount;

        // 2. Validate Migration
        $migrateTo = (int) $request->input('migrate_to', 0);

        if ($migrateTo > 0) {
            if ($migrateTo === $id) {
                $this->json(['error' => 'Cannot migrate to self'], 400);
                return;
            }
            if (!AcquisitionStatus::find($migrateTo)) {
                $this->json(['error' => 'Destination not found'], 400);
                return;
            }
        }

        // 3. Conflict
        if ($totalDependencies > 0 && $migrateTo === 0) {
            $this->json([
                'requires_migration' => true,
                'message' => "Used by {$toyCount} toys and {$itemCount} items.",
                'options_url' => "acquisition-status/migrate-on-delete-options?exclude={$id}"
            ], 409);
            return;
        }

        $db = Database::getInstance();
        try {
            $db->beginTransaction();

            if ($totalDependencies > 0 && $migrateTo > 0) {
                if ($toyCount > 0) CollectionToy::migrateAcquisitionStatus($id, $migrateTo);
                if ($itemCount > 0) CollectionToyItem::migrateAcquisitionStatus($id, $migrateTo);
            }

            AcquisitionStatus::delete($id);
            $db->commit();
            $this->json(['success' => true]);

        } catch (\Exception $e) {
            $db->rollBack();
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function migrateOnDeleteOptions(Request $request): void
    {
        $exclude = (int) $request->input('exclude', 0);
        $db = Database::getInstance();
        $sql = "SELECT id, name FROM meta_acquisition_statuses WHERE id != ? ORDER BY sort_order ASC";
        $options = $db->query($sql, [$exclude])->fetchAll(\PDO::FETCH_ASSOC);
        $this->json($options);
    }
}
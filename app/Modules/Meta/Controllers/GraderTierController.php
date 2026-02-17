<?php
namespace App\Modules\Meta\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Kernel\Database\Database;
use App\Modules\Meta\Models\GraderTier;

// Placeholders for Collection module
use App\Modules\Collection\Models\CollectionToy;
use App\Modules\Collection\Models\CollectionToyItem;

class GraderTierController extends Controller
{
    public function index(Request $request): void
    {
        $this->render('grader_tier_index', ['title' => 'Grader Tiers']);
    }

    public function list(Request $request): void
    {
        $page = (int) $request->input('page', 1);
        $perPage = 20;
        $search = trim($request->input('q', ''));

        $data = GraderTier::getPaginated($page, $perPage, $search);

        $this->renderPartial('grader_tier_list', [
            'tiers' => $data['items'],
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
        
        // VALIDATION
        if ($name === '') {
            $this->json(['field' => 'name', 'message' => 'Name is required'], 422);
            return;
        }
        if (mb_strlen($name) > 50) {
            $this->json(['field' => 'name', 'message' => 'Name cannot exceed 50 characters'], 422);
            return;
        }

        $slug = GraderTier::validateUniqueSlug($request->input('slug'), $name);
        if ($slug === null) {
            $this->json(['field' => 'slug', 'message' => 'Slug already exists'], 422);
            return;
        }

        GraderTier::create([
            'name' => $name,
            'slug' => $slug,
            'sort_order' => (int) $request->input('sort_order', 0)
        ]);

        $this->json(['success' => true]);
    }

    public function update(Request $request, int $id): void
    {
        if (!GraderTier::find($id)) {
            $this->json(['error' => 'Record not found'], 404);
            return;
        }

        $name = trim($request->input('name', ''));
        
        if ($name === '') {
            $this->json(['field' => 'name', 'message' => 'Name is required'], 422);
            return;
        }
        if (mb_strlen($name) > 50) {
            $this->json(['field' => 'name', 'message' => 'Name cannot exceed 50 characters'], 422);
            return;
        }

        $slug = GraderTier::validateUniqueSlug($request->input('slug'), $name, $id);
        if ($slug === null) {
            $this->json(['field' => 'slug', 'message' => 'Slug already exists'], 422);
            return;
        }

        GraderTier::update($id, [
            'name' => $name,
            'slug' => $slug,
            'sort_order' => (int) $request->input('sort_order', 0)
        ]);

        $updated = GraderTier::find($id);
        ob_start();
        $this->renderPartial('grader_tier_row', ['tier' => $updated]);
        
        $this->json(['success' => true, 'row_html' => ob_get_clean()]);
    }

    public function destroy(Request $request, int $id): void
    {
        // 1. Check Dependencies
        $toyCount = 0;
        $itemCount = 0;

        if (class_exists(CollectionToy::class)) {
            $toyCount = CollectionToy::countByGraderTier($id);
        }
        if (class_exists(CollectionToyItem::class)) {
            $itemCount = CollectionToyItem::countByGraderTier($id);
        }

        $totalDependencies = $toyCount + $itemCount;

        // 2. Validate Migration
        $migrateTo = (int) $request->input('migrate_to', 0);

        if ($migrateTo > 0) {
            if ($migrateTo === $id) {
                $this->json(['error' => 'Cannot migrate to self'], 400);
                return;
            }
            if (!GraderTier::find($migrateTo)) {
                $this->json(['error' => 'Destination not found'], 400);
                return;
            }
        }

        // 3. Conflict
        if ($totalDependencies > 0 && $migrateTo === 0) {
            $this->json([
                'requires_migration' => true,
                'message' => "Used by {$toyCount} toys and {$itemCount} items.",
                'options_url' => "grader-tier/migrate-on-delete-options?exclude={$id}"
            ], 409);
            return;
        }

        $db = Database::getInstance();
        try {
            $db->beginTransaction();

            if ($totalDependencies > 0 && $migrateTo > 0) {
                if ($toyCount > 0) CollectionToy::migrateGraderTier($id, $migrateTo);
                if ($itemCount > 0) CollectionToyItem::migrateGraderTier($id, $migrateTo);
            }

            GraderTier::delete($id);
            $db->commit();
            $this->json(['success' => true]);

        } catch (\Exception $e) {
            $db->rollBack();
            error_log('Delete failed: ' . $e->getMessage());
            $this->json(['error' => 'Failed to delete record.'], 500);
        }
    }

    public function migrateOnDeleteOptions(Request $request): void
    {
        $exclude = (int) $request->input('exclude', 0);
        $db = Database::getInstance();
        $sql = "SELECT id, name FROM meta_grader_tiers WHERE id != ? ORDER BY sort_order ASC";
        $options = $db->query($sql, [$exclude])->fetchAll(\PDO::FETCH_ASSOC);
        $this->json($options);
    }
}
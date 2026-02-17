<?php
namespace App\Modules\Meta\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Kernel\Database\Database;
use App\Modules\Meta\Models\ConditionGrade;

// Placeholders for Collection module
use App\Modules\Collection\Models\CollectionToy;
use App\Modules\Collection\Models\CollectionToyItem;

class ConditionGradeController extends Controller
{
    public function index(Request $request): void
    {
        $this->render('condition_grade_index', ['title' => 'Condition Grades']);
    }

    public function list(Request $request): void
    {
        $page = (int) $request->input('page', 1);
        $perPage = 20;
        $search = trim($request->input('q', ''));

        $data = ConditionGrade::getPaginated($page, $perPage, $search);

        $this->renderPartial('condition_grade_list', [
            'grades' => $data['items'],
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
        $abbr = trim($request->input('abbreviation', ''));
        
        // VALIDATION
        if ($name === '') {
            $this->json(['field' => 'name', 'message' => 'Name is required'], 422);
            return;
        }
        if (mb_strlen($name) > 50) {
            $this->json(['field' => 'name', 'message' => 'Name cannot exceed 50 characters'], 422);
            return;
        }
        if (mb_strlen($abbr) > 10) {
            $this->json(['field' => 'abbreviation', 'message' => 'Abbreviation cannot exceed 10 characters'], 422);
            return;
        }

        $slug = ConditionGrade::validateUniqueSlug($request->input('slug'), $name);
        if ($slug === null) {
            $this->json(['field' => 'slug', 'message' => 'Slug already exists'], 422);
            return;
        }

        ConditionGrade::create([
            'name' => $name,
            'slug' => $slug,
            'abbreviation' => $abbr ?: null,
            'description' => trim($request->input('description', '')),
            'sort_order' => (int) $request->input('sort_order', 0)
        ]);

        $this->json(['success' => true]);
    }

    public function update(Request $request, int $id): void
    {
        if (!ConditionGrade::find($id)) {
            $this->json(['error' => 'Record not found'], 404);
            return;
        }

        $name = trim($request->input('name', ''));
        $abbr = trim($request->input('abbreviation', ''));

        // VALIDATION
        if ($name === '') {
            $this->json(['field' => 'name', 'message' => 'Name is required'], 422);
            return;
        }
        if (mb_strlen($name) > 50) {
            $this->json(['field' => 'name', 'message' => 'Name cannot exceed 50 characters'], 422);
            return;
        }
        if (mb_strlen($abbr) > 10) {
            $this->json(['field' => 'abbreviation', 'message' => 'Abbreviation cannot exceed 10 characters'], 422);
            return;
        }

        $slug = ConditionGrade::validateUniqueSlug($request->input('slug'), $name, $id);
        if ($slug === null) {
            $this->json(['field' => 'slug', 'message' => 'Slug already exists'], 422);
            return;
        }

        ConditionGrade::update($id, [
            'name' => $name,
            'slug' => $slug,
            'abbreviation' => $abbr ?: null,
            'description' => trim($request->input('description', '')),
            'sort_order' => (int) $request->input('sort_order', 0)
        ]);

        $updated = ConditionGrade::find($id);
        ob_start();
        $this->renderPartial('condition_grade_row', ['g' => $updated]);
        
        $this->json(['success' => true, 'row_html' => ob_get_clean()]);
    }

    public function destroy(Request $request, int $id): void
    {
        // 1. Check Dependencies
        $toyCount = 0;
        $itemCount = 0;

        if (class_exists(CollectionToy::class)) {
            $toyCount = CollectionToy::countByConditionGrade($id);
        }
        if (class_exists(CollectionToyItem::class)) {
            $itemCount = CollectionToyItem::countByConditionGrade($id);
        }

        $totalDependencies = $toyCount + $itemCount;

        // 2. Validate Migration
        $migrateTo = (int) $request->input('migrate_to', 0);

        if ($migrateTo > 0) {
            if ($migrateTo === $id) {
                $this->json(['error' => 'Cannot migrate to self'], 400);
                return;
            }
            if (!ConditionGrade::find($migrateTo)) {
                $this->json(['error' => 'Destination not found'], 400);
                return;
            }
        }

        // 3. Conflict
        if ($totalDependencies > 0 && $migrateTo === 0) {
            $this->json([
                'requires_migration' => true,
                'message' => "Used by {$toyCount} toys and {$itemCount} items.",
                'options_url' => "condition-grade/migrate-on-delete-options?exclude={$id}"
            ], 409);
            return;
        }

        $db = Database::getInstance();
        try {
            $db->beginTransaction();

            if ($totalDependencies > 0 && $migrateTo > 0) {
                if ($toyCount > 0) CollectionToy::migrateConditionGrade($id, $migrateTo);
                if ($itemCount > 0) CollectionToyItem::migrateConditionGrade($id, $migrateTo);
            }

            ConditionGrade::delete($id);
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
        $sql = "SELECT id, name FROM meta_condition_grades WHERE id != ? ORDER BY sort_order ASC";
        $options = $db->query($sql, [$exclude])->fetchAll(\PDO::FETCH_ASSOC);
        $this->json($options);
    }
}
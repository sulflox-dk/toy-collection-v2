<?php
namespace App\Modules\Meta\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Kernel\Database\Database;
use App\Modules\Meta\Models\GradingCompany;

// Placeholders for Collection module
use App\Modules\Collection\Models\CollectionToy;
use App\Modules\Collection\Models\CollectionToyItem;

class GradingCompanyController extends Controller
{
    public function index(Request $request): void
    {
        $this->render('grading_company_index', ['title' => 'Grading Companies']);
    }

    public function list(Request $request): void
    {
        $page = (int) $request->input('page', 1);
        $perPage = 20;
        $search = trim($request->input('q', ''));

        $data = GradingCompany::getPaginated($page, $perPage, $search);

        $this->renderPartial('grading_company_list', [
            'companies' => $data['items'],
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
        $website = trim($request->input('website', ''));
        
        // VALIDATION
        if ($name === '') {
            $this->json(['field' => 'name', 'message' => 'Name is required'], 422);
            return;
        }
        if (mb_strlen($name) > 50) {
            $this->json(['field' => 'name', 'message' => 'Name cannot exceed 50 characters'], 422);
            return;
        }

        if ($website !== '' && !filter_var($website, FILTER_VALIDATE_URL)) {
            $this->json(['field' => 'website', 'message' => 'Please enter a valid URL (include http:// or https://)'], 422);
            return;
        }

        $slug = GradingCompany::validateUniqueSlug($request->input('slug'), $name);
        if ($slug === null) {
            $this->json(['field' => 'slug', 'message' => 'Slug already exists'], 422);
            return;
        }

        GradingCompany::create([
            'name' => $name,
            'slug' => $slug,
            'website' => $website ?: null
        ]);

        $this->json(['success' => true]);
    }

    public function update(Request $request, int $id): void
    {
        if (!GradingCompany::find($id)) {
            $this->json(['error' => 'Record not found'], 404);
            return;
        }

        $name = trim($request->input('name', ''));
        $website = trim($request->input('website', ''));

        // VALIDATION
        if ($name === '') {
            $this->json(['field' => 'name', 'message' => 'Name is required'], 422);
            return;
        }
        if (mb_strlen($name) > 50) {
            $this->json(['field' => 'name', 'message' => 'Name cannot exceed 50 characters'], 422);
            return;
        }

        if ($website !== '' && !filter_var($website, FILTER_VALIDATE_URL)) {
            $this->json(['field' => 'website', 'message' => 'Please enter a valid URL (include http:// or https://)'], 422);
            return;
        }

        $slug = GradingCompany::validateUniqueSlug($request->input('slug'), $name, $id);
        if ($slug === null) {
            $this->json(['field' => 'slug', 'message' => 'Slug already exists'], 422);
            return;
        }

        GradingCompany::update($id, [
            'name' => $name,
            'slug' => $slug,
            'website' => $website ?: null
        ]);

        $updated = GradingCompany::find($id);
        ob_start();
        $this->renderPartial('grading_company_row', ['company' => $updated]);
        
        $this->json(['success' => true, 'row_html' => ob_get_clean()]);
    }

    public function destroy(Request $request, int $id): void
    {
        // 1. Check Dependencies
        $toyCount = 0;
        $itemCount = 0;

        if (class_exists(CollectionToy::class)) {
            $toyCount = CollectionToy::countByGradingCompany($id);
        }
        if (class_exists(CollectionToyItem::class)) {
            $itemCount = CollectionToyItem::countByGradingCompany($id);
        }

        $totalDependencies = $toyCount + $itemCount;

        // 2. Validate Migration
        $migrateTo = (int) $request->input('migrate_to', 0);

        if ($migrateTo > 0) {
            if ($migrateTo === $id) {
                $this->json(['error' => 'Cannot migrate to self'], 400);
                return;
            }
            if (!GradingCompany::find($migrateTo)) {
                $this->json(['error' => 'Destination not found'], 400);
                return;
            }
        }

        // 3. Conflict
        if ($totalDependencies > 0 && $migrateTo === 0) {
            $this->json([
                'requires_migration' => true,
                'message' => "Used by {$toyCount} toys and {$itemCount} items.",
                'options_url' => "grading-company/migrate-on-delete-options?exclude={$id}"
            ], 409);
            return;
        }

        $db = Database::getInstance();
        try {
            $db->beginTransaction();

            if ($totalDependencies > 0 && $migrateTo > 0) {
                if ($toyCount > 0) CollectionToy::migrateGradingCompany($id, $migrateTo);
                if ($itemCount > 0) CollectionToyItem::migrateGradingCompany($id, $migrateTo);
            }

            GradingCompany::delete($id);
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
        $sql = "SELECT id, name FROM meta_grading_companies WHERE id != ? ORDER BY name ASC";
        $options = $db->query($sql, [$exclude])->fetchAll(\PDO::FETCH_ASSOC);
        $this->json($options);
    }
}
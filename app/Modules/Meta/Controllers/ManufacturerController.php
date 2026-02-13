<?php
namespace App\Modules\Meta\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Kernel\Database\Database;
use App\Modules\Meta\Models\Manufacturer;
use App\Modules\Meta\Models\ToyLine;
use App\Modules\Catalog\Models\CatalogToy;

class ManufacturerController extends Controller
{
    public function index(Request $request): void
    {
        $manufacturers = Manufacturer::all();

        $this->render('manufacturer_index', [
            'title'         => 'Manufacturers',
            'manufacturers' => $manufacturers,
        ]);
    }
    
    /**
     * Returns the HTML Table of manufacturers (for AJAX)
     * GET /manufacturer/list?page=1&q=searchterm
     */
    public function list(Request $request): void
    {
        $page = (int) $request->input('page', 1);
        $perPage = 20; // Keep at 2 for testing pagination!
        $search = trim($request->input('q', ''));
        $visibility = trim($request->input('visibility', '')); // <-- NEW

        // Pass the new parameter to the model
        $data = Manufacturer::getPaginatedWithLineCount($page, $perPage, $search, $visibility);

        $this->renderPartial('manufacturer_list', [
            'manufacturers' => $data['items'],
            'pagination' => [
                'current' => $page,
                'total'   => $data['totalPages'],
                'count'   => $data['total']
            ]
        ]);
    }

    /**
     * Create a new manufacturer
     * POST /manufacturer
     */
    public function store(Request $request): void
    {
        $name = trim($request->input('name', ''));
        if ($name === '') {
            $this->json(['error' => 'Name is required'], 422);
            return;
        }

        // 1. Validate Slug (New Item)
        $slug = $this->validateSlug($request->input('slug'), $name);
        
        // FIX: strict array check
        if (is_array($slug)) {
            $this->json($slug, 422);
            return;
        }

        // 2. Create
        Manufacturer::create([
            'name' => $name,
            'slug' => $slug,
            'show_on_dashboard' => $request->input('show_on_dashboard') ? 1 : 0,
        ]);

        $this->json(['success' => true]);
    }

    /**
     * Update an existing manufacturer
     * PUT /manufacturer/{id}
     */
    public function update(Request $request, string $id): void
    {
        $name = trim($request->input('name', ''));
        if ($name === '') {
            $this->json(['error' => 'Name is required'], 422);
            return;
        }


        if (mb_strlen($name) > 255) {
            $this->json(['error' => 'Name must be 255 characters or fewer'], 422);
            return;
        }

        $slug = trim(strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name)), '-');
        if ($slug === '') {
            $this->json(['error' => 'Name must contain at least one alphanumeric character'], 422);
            return;
        }

        // Check for duplicate slug (excluding current record on update)
        $existing = Manufacturer::firstWhere('slug', $slug);
        if ($existing && (string) $existing['id'] !== (string) $id) {
            $this->json(['error' => 'A manufacturer with this name already exists'], 422);
            return;
        }

        $showOnDashboard = $request->input('show_on_dashboard') ? 1 : 0;


        // 2. Update
        Manufacturer::update((int) $id, [
            'name' => $name,
            'slug' => $slug,
            'show_on_dashboard' => $request->input('show_on_dashboard') ? 1 : 0,
        ]);

        // 3. Return Updated Row HTML
        $updatedItem = Manufacturer::find((int) $id);
        
        ob_start();
        $this->renderPartial('manufacturer_row', ['m' => $updatedItem]);
        $rowHtml = ob_get_clean();

        $this->json([
            'success' => true,
            'row_html' => $rowHtml
        ]);
    }

    // ... destroy() method remains unchanged ...

    /**
     * Helper to validate and generate unique slugs
     * Returns STRING (valid slug) or ARRAY (error)
     */
    private function validateSlug(?string $inputSlug, string $name, ?int $excludeId = null): string|array
    {
        // 1. Generate Slug
        $rawSlug = trim($inputSlug ?? '');
        $source = ($rawSlug === '') ? $name : $rawSlug;
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $source));
        $slug = trim($slug, '-');

        // 2. Check Uniqueness (Using COUNT is safer/simpler here)
        $db = Database::getInstance();
        
        $sql = "SELECT COUNT(*) FROM meta_manufacturers WHERE slug = ?";
        $params = [$slug];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $count = (int) $db->query($sql, $params)->fetchColumn();

        if ($count > 0) {
            return [
                'error' => "The slug '$slug' is already taken.",
                'field' => 'slug' 
            ];
        }

        return $slug;
    }

    /**
     * Delete a manufacturer or request a migration if it has dependents.
     * DELETE /manufacturer/{id}?migrate_to={new_id}
     */
    public function destroy(Request $request, string $id): void
    {
        $mId = (int) $id;

        // 1. Check BOTH dependent tables using our new Models!
        $toyLineCount = ToyLine::countByManufacturer($mId);
        $catalogToyCount = CatalogToy::countByManufacturer($mId);
        $totalCount = $toyLineCount + $catalogToyCount;

        $migrateTo = (int) $request->input('migrate_to', 0);

        // 2. Has dependents, but no migration target provided?
        if ($totalCount > 0 && $migrateTo === 0) {
            $this->json([
                'requires_migration' => true,
                'message' => "This manufacturer is linked to {$toyLineCount} toy line(s) and {$catalogToyCount} catalog toy(s). Please reassign them to another manufacturer before deleting.",
                'options_url' => "manufacturer/migrate-on-delete-options?exclude={$mId}" 
            ], 409);
            return;
        }

        // 3. User provided a migration target? Reassign items!
        if ($totalCount > 0 && $migrateTo > 0) {
            ToyLine::migrateManufacturer($mId, $migrateTo);
            CatalogToy::migrateManufacturer($mId, $migrateTo);
        }

        // 4. Safe to delete now
        Manufacturer::delete($mId);
        $this->json(['success' => true]);
    }

    /**
     * Returns a JSON list of manufacturers for the Migration Dropdown
     * GET /manufacturer/migrate-on-delete-options?exclude={id}
     */
    public function migrateOnDeleteOptions(Request $request): void
    {
        $exclude = (int) $request->input('exclude', 0);
        $db = Database::getInstance();
        
        $sql = "SELECT id, name FROM meta_manufacturers";
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
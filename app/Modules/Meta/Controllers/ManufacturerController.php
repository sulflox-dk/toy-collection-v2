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
         $this->render('manufacturer_index', [
            'title'         => 'Manufacturers'
        ]);
    }
    
    /**
     * Returns the HTML Table of manufacturers (for AJAX)
     * GET /manufacturer/list?page=1&q=searchterm
     */
    public function list(Request $request): void
    {
        $page = (int) $request->input('page', 1);
        $perPage = 20;
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
            $this->json(['field' => 'name', 'message' => 'Name is required'], 422);
            return;
        }

        // FIXED: Added length validation
        if (mb_strlen($name) > 255) {
            $this->json(['field' => 'name', 'message' => 'Name cannot exceed 255 characters'], 422);
            return;
        }

         $slug = Manufacturer::validateUniqueSlug($request->input('slug'), $name, 0);
        
        // Check if slug validation failed
        if ($slug === null) {
            $this->json(['field' => 'slug', 'message' => 'This slug is already in use.'], 422);
            return;
        }

        $showOnDashboard = filter_var($request->input('show_on_dashboard'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

        // 2. Create
        Manufacturer::create([
            'name' => $name,
            'slug' => $slug,
            'show_on_dashboard' => $showOnDashboard,
        ]);

        $this->json(['success' => true]);
    }

    /**
     * Update an existing manufacturer
     * POST /manufacturer/{id} (via _method=PUT)
     */
    public function update(Request $request, int $id): void
    {
        $existing = Manufacturer::find($id);
        if (!$existing) {
            $this->json(['error' => 'Manufacturer not found'], 404);
            return;
        }

        $name = trim($request->input('name', ''));
        
        if ($name === '') {
            $this->json(['field' => 'name', 'message' => 'Name is required'], 422);
            return;
        }

        // FIXED: Ensure length validation is also present/consistent here
        if (mb_strlen($name) > 255) {
            $this->json(['field' => 'name', 'message' => 'Name cannot exceed 255 characters'], 422);
            return;
        }

        $slug = Manufacturer::validateUniqueSlug($request->input('slug'), $name, $id);

        if ($slug === null) {
            $this->json(['field' => 'slug', 'message' => 'This slug is already in use.'], 422);
            return;
        }

        Manufacturer::update($id, [
            'name' => $name,
            'slug' => $slug,
            'show_on_dashboard' => filter_var($request->input('show_on_dashboard'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
        ]);

        // 4. Prepare HTML for the row update
        // We reuse the $existing ID we verified earlier
        $updatedItem = Manufacturer::find($id);
        
        $db = Database::getInstance();
        $updatedItem['lines_count'] = $db->query(
            "SELECT COUNT(*) FROM meta_toy_lines WHERE manufacturer_id = ?", 
            [$id]
        )->fetchColumn();

        ob_start();
        $this->renderPartial('manufacturer_row', ['m' => $updatedItem]);
        $html = ob_get_clean();

        $this->json([
            'success'  => true, 
            'row_html' => $html
        ]);
    }

    /**
     * Delete a manufacturer
     * DELETE /manufacturer/{id}
     */
    public function destroy(Request $request, int $id): void
    {
        // 1. Check for dependencies
        $toyLineCount = ToyLine::countByManufacturer($id);
        $catalogToyCount = CatalogToy::countByManufacturer($id);
        $totalCount = $toyLineCount + $catalogToyCount;

        // 2. Validate Migration Request
        $migrateTo = (int) $request->input('migrate_to', 0);

        // SECURITY FIX: If migration is requested, validate the target!
        if ($migrateTo > 0) {
            // A. Prevent migrating to self
            if ($migrateTo === $id) {
                $this->json(['error' => 'Cannot migrate items to the manufacturer being deleted.'], 400);
                return;
            }
            // B. Verify target exists
            $target = Manufacturer::find($migrateTo);
            if (!$target) {
                $this->json(['error' => 'The selected manufacturer for reassignment does not exist.'], 400);
                return;
            }
        }

        // 3. Handle Migration Requirement (409 Conflict)
        if ($totalCount > 0 && $migrateTo === 0) {
            $this->json([
                'requires_migration' => true,
                'message' => "This manufacturer is linked to {$toyLineCount} toy line(s) and {$catalogToyCount} catalog toy(s). Please reassign them to another manufacturer before deleting.",
                'options_url' => "manufacturer/migrate-on-delete-options?exclude={$id}"
            ], 409);
            return;
        }

        $db = Database::getInstance();

        try {
            // START TRANSACTION 🛡️
            $db->beginTransaction();

            // 4. Migrate items if requested (and validated)
            if ($totalCount > 0 && $migrateTo > 0) {
                ToyLine::migrateManufacturer($id, $migrateTo);
                CatalogToy::migrateManufacturer($id, $migrateTo);
            }

            // 5. Delete the record
            Manufacturer::delete($id);

            // COMMIT TRANSACTION ✅
            $db->commit();
            
            $this->json(['success' => true]);

        } catch (\Exception $e) {
            // ROLLBACK ON FAILURE
            $db->rollBack();
            error_log('Delete failed: ' . $e->getMessage());
            $this->json(['error' => 'Failed to delete record. Please try again.'], 500);
        }
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
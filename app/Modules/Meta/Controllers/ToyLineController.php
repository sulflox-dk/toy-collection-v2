<?php
namespace App\Modules\Meta\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Kernel\Database\Database;
use App\Modules\Meta\Models\ToyLine;
use App\Modules\Meta\Models\Manufacturer; // For dropdowns
use App\Modules\Meta\Models\Universe;     // For dropdowns
use App\Modules\Catalog\Models\CatalogToy; // For dependency check

class ToyLineController extends Controller
{
    public function index(Request $request): void
    {
        // Fetch lists for the modal dropdowns
        $db = Database::getInstance();
        $manufacturers = $db->query("SELECT id, name FROM meta_manufacturers ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $universes = $db->query("SELECT id, name FROM meta_universes ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);

        $this->render('toy_line_index', [
            'title' => 'Toy Lines',
            'manufacturers' => $manufacturers,
            'universes' => $universes
        ]);
    }

    /**
     * Returns the HTML Table of Toy Lines (for AJAX)
     */
    public function list(Request $request): void
    {
        $page = (int) $request->input('page', 1);
        $perPage = 20;
        $search = trim($request->input('q', ''));
        $visibility = trim($request->input('visibility', ''));
        
        // Retrieve new filters
        $manufacturerId = (int) $request->input('manufacturer_id', 0);
        $universeId = (int) $request->input('universe_id', 0);

        // Pass them to the model
        $data = ToyLine::getPaginatedWithDetails(
            $page, 
            $perPage, 
            $search, 
            $visibility,
            $manufacturerId,
            $universeId
        );

        $this->renderPartial('toy_line_list', [
            'toyLines' => $data['items'],
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

        // Validate Dropdowns
        $manufacturerId = (int) $request->input('manufacturer_id');
        $universeId = (int) $request->input('universe_id');

        if ($manufacturerId <= 0) {
            $this->json(['field' => 'manufacturer_id', 'message' => 'Please select a manufacturer'], 422);
            return;
        }
        if ($universeId <= 0) {
            $this->json(['field' => 'universe_id', 'message' => 'Please select a universe'], 422);
            return;
        }

        $slug = ToyLine::validateUniqueSlug($request->input('slug'), $name);
        if ($slug === null) {
            $this->json(['field' => 'slug', 'message' => 'This slug is already in use.'], 422);
            return;
        }

        ToyLine::create([
            'name' => $name,
            'slug' => $slug,
            'manufacturer_id' => $manufacturerId,
            'universe_id' => $universeId,
            'show_on_dashboard' => $request->input('show_on_dashboard') !== null ? 1 : 0
        ]);

        $this->json(['success' => true]);
    }

    public function update(Request $request, int $id): void
    {
        if (!ToyLine::find($id)) {
            $this->json(['error' => 'Toy Line not found'], 404);
            return;
        }

        $name = trim($request->input('name', ''));
        
        $slug = ToyLine::validateUniqueSlug($request->input('slug'), $name, $id);
        if ($slug === null) {
            $this->json(['field' => 'slug', 'message' => 'This slug is already in use.'], 422);
            return;
        }

        ToyLine::update($id, [
            'name' => $name,
            'slug' => $slug,
            'manufacturer_id' => (int) $request->input('manufacturer_id'),
            'universe_id' => (int) $request->input('universe_id'),
            'show_on_dashboard' => $request->input('show_on_dashboard') ? 1 : 0
        ]);

        // Re-fetch with joined names for the row update
        $db = Database::getInstance();
        $sql = "
            SELECT t.*, m.name as manufacturer_name, u.name as universe_name
            FROM meta_toy_lines t
            LEFT JOIN meta_manufacturers m ON t.manufacturer_id = m.id
            LEFT JOIN meta_universes u ON t.universe_id = u.id
            WHERE t.id = ?
        ";
        $updated = $db->query($sql, [$id])->fetch(\PDO::FETCH_ASSOC);

        ob_start();
        $this->renderPartial('toy_line_row', ['t' => $updated]);
        
        $this->json(['success' => true, 'row_html' => ob_get_clean()]);
    }

    public function destroy(Request $request, int $id): void
    {
        // 1. Check for dependencies (Catalog Toys)
        // Ensure CatalogToy has `countByToyLine` method or return 0
        $catalogToyCount = method_exists(CatalogToy::class, 'countByToyLine') ? CatalogToy::countByToyLine($id) : 0;
        
        // 2. Validate Migration Request
        $migrateTo = (int) $request->input('migrate_to', 0);

        if ($migrateTo > 0) {
            if ($migrateTo === $id) {
                $this->json(['error' => 'Cannot migrate items to the toy line being deleted.'], 400);
                return;
            }
            $target = ToyLine::find($migrateTo);
            if (!$target) {
                $this->json(['error' => 'The selected toy line does not exist.'], 400);
                return;
            }
        }

        // 3. Handle Migration Requirement (409 Conflict)
        if ($catalogToyCount > 0 && $migrateTo === 0) {
            $this->json([
                'requires_migration' => true,
                'message' => "This toy line is linked to {$catalogToyCount} catalog toy(s). Please reassign them to another toy line before deleting.",
                'options_url' => "toy-line/migrate-on-delete-options?exclude={$id}"
            ], 409);
            return;
        }

        $db = Database::getInstance();

        try {
            $db->beginTransaction();

            // 4. Migrate items if requested
            if ($catalogToyCount > 0 && $migrateTo > 0) {
                if (method_exists(CatalogToy::class, 'migrateToyLine')) {
                    CatalogToy::migrateToyLine($id, $migrateTo);
                }
            }

            // 5. Delete
            ToyLine::delete($id);

            $db->commit();
            $this->json(['success' => true]);

        } catch (\Exception $e) {
            $db->rollBack();
            $this->json(['error' => 'Delete failed: ' . $e->getMessage()], 500);
        }
    }

    public function migrateOnDeleteOptions(Request $request): void
    {
        $exclude = (int) $request->input('exclude', 0);
        $db = Database::getInstance();
        
        $sql = "SELECT id, name FROM meta_toy_lines";
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
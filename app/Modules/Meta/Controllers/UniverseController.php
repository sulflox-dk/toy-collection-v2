<?php
namespace App\Modules\Meta\Controllers;

use App\Kernel\Database\Database;
use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Modules\Meta\Models\Universe;
use App\Modules\Meta\Models\ToyLine; 
use App\Modules\Catalog\Models\CatalogToy;

class UniverseController extends Controller
{
    public function index(Request $request): void
    {
        $this->render('universe_index', [
            'title' => 'Universes'
        ]);
    }

    public function list(Request $request): void
    {
        $page = (int) $request->input('page', 1);
        $perPage = 2;
        $search = trim($request->input('q', ''));
        $visibility = trim($request->input('visibility', ''));

        $data = Universe::getPaginatedWithLineCount($page, $perPage, $search, $visibility);

        $this->renderPartial('universe_list', [
            'universes' => $data['items'],
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

        if (mb_strlen($name) > 255) {
            $this->json(['field' => 'name', 'message' => 'Name too long'], 422);
            return;
        }

        $slug = Universe::validateUniqueSlug($request->input('slug'), $name, 0);
        
        // Check if slug validation failed
        if ($slug === null) {
            $this->json(['field' => 'slug', 'message' => 'This slug is already in use.'], 422);
            return;
        }

        Universe::create([
            'name' => $name,
            'slug' => $slug,
            'description' => trim($request->input('description', '')),
            'show_on_dashboard' => $request->input('show_on_dashboard') !== null ? 1 : 0
        ]);

        $this->json(['success' => true]);
    }

    public function update(Request $request, int $id): void
    {
        if (!Universe::find($id)) {
            $this->json(['error' => 'Universe not found'], 404);
            return;
        }

        $name = trim($request->input('name', ''));

        $slug = Universe::validateUniqueSlug($request->input('slug'), $name, $id);
        
        // Check if slug validation failed
        if ($slug === null) {
            $this->json(['field' => 'slug', 'message' => 'This slug is already in use.'], 422);
            return;
        }

        Universe::update($id, [
            'name' => $name,
            'slug' => $slug,
            'description' => trim($request->input('description', '')),
            'show_on_dashboard' => $request->input('show_on_dashboard') ? 1 : 0
        ]);

        $updated = Universe::find($id);
        ob_start();
        $this->renderPartial('universe_row', ['u' => $updated]);
        
        $this->json(['success' => true, 'row_html' => ob_get_clean()]);
    }

    /**
     * Delete a universe
     * DELETE /universe/{id}
     */
    public function destroy(Request $request, int $id): void
    {
        // 1. Check for dependencies
        // (Ensure these methods exist in your ToyLine/CatalogToy models, or return 0 for now)
        $toyLineCount = method_exists(ToyLine::class, 'countByUniverse') ? ToyLine::countByUniverse($id) : 0;
        $catalogToyCount = method_exists(CatalogToy::class, 'countByUniverse') ? CatalogToy::countByUniverse($id) : 0;
        $totalCount = $toyLineCount + $catalogToyCount;

        // 2. Validate Migration Request
        $migrateTo = (int) $request->input('migrate_to', 0);

        if ($migrateTo > 0) {
            // A. Prevent migrating to self
            if ($migrateTo === $id) {
                $this->json(['error' => 'Cannot migrate items to the universe being deleted.'], 400);
                return;
            }
            // B. Verify target exists
            $target = Universe::find($migrateTo);
            if (!$target) {
                $this->json(['error' => 'The selected universe for reassignment does not exist.'], 400);
                return;
            }
        }

        // 3. Handle Migration Requirement (409 Conflict)
        if ($totalCount > 0 && $migrateTo === 0) {
            $this->json([
                'requires_migration' => true,
                'message' => "This universe is linked to {$toyLineCount} toy line(s) and {$catalogToyCount} catalog toy(s). Please reassign them to another universe before deleting.",
                'options_url' => "universe/migrate-on-delete-options?exclude={$id}"
            ], 409);
            return;
        }

        $db = Database::getInstance();

        try {
            // START TRANSACTION 🛡️
            $db->beginTransaction();

            // 4. Migrate items if requested (and validated)
            if ($totalCount > 0 && $migrateTo > 0) {
                if (method_exists(ToyLine::class, 'migrateUniverse')) {
                    ToyLine::migrateUniverse($id, $migrateTo);
                }
                if (method_exists(CatalogToy::class, 'migrateUniverse')) {
                    CatalogToy::migrateUniverse($id, $migrateTo);
                }
            }

            // 5. Delete the record
            Universe::delete($id);

            // COMMIT TRANSACTION ✅
            $db->commit();
            
            $this->json(['success' => true]);

        } catch (\Exception $e) {
            // ROLLBACK ON FAILURE ❌
            $db->rollBack();
            $this->json(['error' => 'Delete failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Returns a JSON list of universes for the Migration Dropdown
     * GET /universe/migrate-on-delete-options?exclude={id}
     */
    public function migrateOnDeleteOptions(Request $request): void
    {
        $exclude = (int) $request->input('exclude', 0);
        $db = Database::getInstance();
        
        $sql = "SELECT id, name FROM meta_universes";
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
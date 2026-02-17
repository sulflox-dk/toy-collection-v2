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
        $perPage = 20;
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
            $this->json(['field' => 'name', 'message' => 'Name cannot exceed 255 characters'], 422);
            return;
        }

        $slug = Universe::validateUniqueSlug($request->input('slug'), $name);
        if ($slug === null) {
            $this->json(['field' => 'slug', 'message' => 'This slug is already in use.'], 422);
            return;
        }

        Universe::create([
            'name' => $name,
            'slug' => $slug,
            'description' => trim($request->input('description', '')),
            'show_on_dashboard' => filter_var($request->input('show_on_dashboard'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0
        ]);

        $this->json(['success' => true]);
    }

    public function update(Request $request, int $id): void
    {
        if (!Universe::find($id)) {
            $this->json(['error' => 'Record not found'], 404);
            return;
        }

        $name = trim($request->input('name', ''));

        if ($name === '') {
            $this->json(['field' => 'name', 'message' => 'Name is required'], 422);
            return;
        }
        if (mb_strlen($name) > 255) {
            $this->json(['field' => 'name', 'message' => 'Name cannot exceed 255 characters'], 422);
            return;
        }

        $slug = Universe::validateUniqueSlug($request->input('slug'), $name, $id);
        if ($slug === null) {
            $this->json(['field' => 'slug', 'message' => 'This slug is already in use.'], 422);
            return;
        }

        Universe::update($id, [
            'name' => $name,
            'slug' => $slug,
            'description' => trim($request->input('description', '')),
            'show_on_dashboard' => filter_var($request->input('show_on_dashboard'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0
        ]);

        // ... refetch ...
        $db = Database::getInstance();
        $sql = "SELECT u.*, COUNT(l.id) as lines_count FROM meta_universes u ...";
        $updated = $db->query($sql, [$id])->fetch(\PDO::FETCH_ASSOC);

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
        // 1. Check Dependencies across ALL 3 Children
        // We use fully qualified names here to ensure no "Class not found" errors
        // if you haven't added them to the 'use' statements at the top.
        $toyLineCount = \App\Modules\Meta\Models\ToyLine::countByUniverse($id);
        $catToyCount  = \App\Modules\Catalog\Models\CatalogToy::countByUniverse($id);
        
        // Ensure EntertainmentSource model is loaded and check its count
        $entSourceCount = 0;
        if (class_exists(\App\Modules\Meta\Models\EntertainmentSource::class)) {
            $entSourceCount = \App\Modules\Meta\Models\EntertainmentSource::countByUniverse($id);
        }

        $totalDependencies = $toyLineCount + $catToyCount + $entSourceCount;

        // 2. Validate Migration Target
        $migrateTo = (int) $request->input('migrate_to', 0);
        
        if ($migrateTo > 0) {
            // Error 1: Trying to migrate to itself
            if ($migrateTo === $id) {
                $this->json(['error' => 'Cannot migrate items to the universe being deleted.'], 400);
                return;
            }
            
            // Error 2: Target does not exist
            if (!Universe::find($migrateTo)) {
                $this->json(['error' => 'The selected destination universe does not exist.'], 400);
                return;
            }
        }

        // 3. Conflict Check (409) - Stop here if we have items but no migration target
        if ($totalDependencies > 0 && $migrateTo === 0) {
            $msg = "This universe is in use by: ";
            $parts = [];
            
            if ($toyLineCount > 0) {
                $parts[] = "{$toyLineCount} toy line(s)";
            }
            if ($entSourceCount > 0) {
                $parts[] = "{$entSourceCount} entertainment source(s)";
            }
            if ($catToyCount > 0) {
                $parts[] = "{$catToyCount} toy(s)";
            }
            
            $msg .= implode(', ', $parts) . ". Please reassign them before deleting.";

            $this->json([
                'requires_migration' => true,
                'message' => $msg,
                // This URL must match your route for the migration dropdown
                'options_url' => "universe/migrate-on-delete-options?exclude={$id}"
            ], 409);
            return;
        }

        // 4. Execute Deletion with Transaction
        $db = Database::getInstance();

        try {
            $db->beginTransaction();

            // Migrate if requested
            if ($totalDependencies > 0 && $migrateTo > 0) {
                
                // A. Migrate Toy Lines
                if ($toyLineCount > 0) {
                    \App\Modules\Meta\Models\ToyLine::migrateUniverse($id, $migrateTo);
                }
                
                // B. Migrate Entertainment Sources
                if ($entSourceCount > 0) {
                    \App\Modules\Meta\Models\EntertainmentSource::migrateUniverse($id, $migrateTo);
                }
                
                // C. Migrate Catalog Toys
                if ($catToyCount > 0) {
                    \App\Modules\Catalog\Models\CatalogToy::migrateUniverse($id, $migrateTo);
                }
            }

            // Finally, delete the Universe
            Universe::delete($id);

            $db->commit();
            $this->json(['success' => true]);

        } catch (\Exception $e) {
            $db->rollBack();
            error_log('Delete failed: ' . $e->getMessage());
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
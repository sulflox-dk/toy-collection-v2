<?php
namespace App\Modules\Meta\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Kernel\Database\Database;
use App\Modules\Meta\Models\EntertainmentSource;
use App\Modules\Meta\Models\Universe;

class EntertainmentSourceController extends Controller
{
    public function index(Request $request): void
    {
        // Fetch Universes for dropdown
        $db = Database::getInstance();
        $universes = $db->query("SELECT id, name FROM meta_universes ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
        
        // Hardcoded Types (matching your Enum)
        $types = ['Movie', 'TV Show', 'Video Game', 'Book', 'Other'];

        $this->render('entertainment_source_index', [
            'title' => 'Entertainment Sources',
            'universes' => $universes,
            'types' => $types
        ]);
    }

    public function list(Request $request): void
    {
        $page = (int) $request->input('page', 1);
        $perPage = 20;
        $search = trim($request->input('q', ''));
        $visibility = trim($request->input('visibility', ''));
        
        $type = trim($request->input('type', ''));
        $universeId = (int) $request->input('universe_id', 0);

        $data = EntertainmentSource::getPaginatedWithDetails(
            $page, 
            $perPage, 
            $search, 
            $visibility,
            $type,
            $universeId
        );

        $this->renderPartial('entertainment_source_list', [
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
        
        // VALIDATION
        if ($name === '') {
            $this->json(['field' => 'name', 'message' => 'Name is required'], 422);
            return;
        }
        if (mb_strlen($name) > 255) {
            $this->json(['field' => 'name', 'message' => 'Name cannot exceed 255 characters'], 422);
            return;
        }

        // ENUM VALIDATION
        $allowedTypes = ['Movie', 'TV Show', 'Video Game', 'Book', 'Other'];
        $type = $request->input('type', 'Movie');
        if (!in_array($type, $allowedTypes)) {
            $this->json(['field' => 'type', 'message' => 'Invalid type selected.'], 422);
            return;
        }

        $universeId = (int) $request->input('universe_id');
        if ($universeId <= 0) {
            $this->json(['field' => 'universe_id', 'message' => 'Please select a universe'], 422);
            return;
        }

        $slug = EntertainmentSource::validateUniqueSlug($request->input('slug'), $name);
        if ($slug === null) {
            $this->json(['field' => 'slug', 'message' => 'This slug is already in use.'], 422);
            return;
        }

        EntertainmentSource::create([
            'name' => $name,
            'slug' => $slug,
            'type' => $type,
            'release_year' => $request->input('release_year') ?: null,
            'description' => trim($request->input('description', '')),
            'universe_id' => $universeId,
            // BOOLEAN LOGIC FIX
            'show_on_dashboard' => filter_var($request->input('show_on_dashboard'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0
        ]);

        $this->json(['success' => true]);
    }

    public function update(Request $request, int $id): void
    {
        if (!EntertainmentSource::find($id)) {
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

        // ENUM VALIDATION
        $allowedTypes = ['Movie', 'TV Show', 'Video Game', 'Book', 'Other'];
        $type = $request->input('type', 'Movie');
        if (!in_array($type, $allowedTypes)) {
            $this->json(['field' => 'type', 'message' => 'Invalid type selected.'], 422);
            return;
        }

        $slug = EntertainmentSource::validateUniqueSlug($request->input('slug'), $name, $id);
        if ($slug === null) {
            $this->json(['field' => 'slug', 'message' => 'This slug is already in use.'], 422);
            return;
        }

        EntertainmentSource::update($id, [
            'name' => $name,
            'slug' => $slug,
            'type' => $type,
            'release_year' => $request->input('release_year') ?: null,
            'description' => trim($request->input('description', '')),
            'universe_id' => (int) $request->input('universe_id'),
            // BOOLEAN LOGIC FIX
            'show_on_dashboard' => filter_var($request->input('show_on_dashboard'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0
        ]);

        $db = Database::getInstance();
        $sql = "SELECT e.*, u.name as universe_name FROM meta_entertainment_sources e LEFT JOIN meta_universes u ON e.universe_id = u.id WHERE e.id = ?";
        $updated = $db->query($sql, [$id])->fetch(\PDO::FETCH_ASSOC);

        ob_start();
        $this->renderPartial('entertainment_source_row', ['s' => $updated]);
        $this->json(['success' => true, 'row_html' => ob_get_clean()]);
    }

    public function destroy(Request $request, int $id): void
    {
        // 1. Check Dependencies (Catalog Toys)
        // We use the full namespace or import CatalogToy at top
        $toyCount = \App\Modules\Catalog\Models\CatalogToy::countByEntertainmentSource($id);
        
        // 2. Validate Migration Request
        $migrateTo = (int) $request->input('migrate_to', 0);

        if ($migrateTo > 0) {
            if ($migrateTo === $id) {
                $this->json(['error' => 'Cannot migrate items to the source being deleted.'], 400);
                return;
            }
            if (!EntertainmentSource::find($migrateTo)) {
                $this->json(['error' => 'The selected destination does not exist.'], 400);
                return;
            }
        }

        // 3. Handle Conflict (409)
        if ($toyCount > 0 && $migrateTo === 0) {
            $this->json([
                'requires_migration' => true,
                'message' => "This entertainment source is linked to {$toyCount} catalog toy(s). Please reassign them before deleting.",
                'options_url' => "entertainment-source/migrate-on-delete-options?exclude={$id}"
            ], 409);
            return;
        }

        $db = Database::getInstance();

        try {
            $db->beginTransaction();

            // 4. Migrate if requested
            if ($toyCount > 0 && $migrateTo > 0) {
                \App\Modules\Catalog\Models\CatalogToy::migrateEntertainmentSource($id, $migrateTo);
            }

            // 5. Delete
            EntertainmentSource::delete($id);

            EntertainmentSource::delete($id);
            $db->commit();
            $this->json(['success' => true]);
        } catch (\Exception $e) {
            $db->rollBack();
            error_log('Delete failed: ' . $e->getMessage());
            $this->json(['error' => 'Failed to delete record.'], 500);
        }
    }

    // NEW METHOD
    public function migrateOnDeleteOptions(Request $request): void
    {
        $exclude = (int) $request->input('exclude', 0);
        $db = Database::getInstance();
        
        $sql = "SELECT id, name FROM meta_entertainment_sources";
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
<?php
namespace App\Modules\Meta\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Kernel\Database\Database;
use App\Modules\Meta\Models\Subject;
use App\Modules\Catalog\Models\CatalogToyItem;

class SubjectController extends Controller
{
    public function index(Request $request): void
    {
        $db = Database::getInstance();
        $universes = $db->query("SELECT id, name FROM meta_universes ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
        
        $types = ['Character','Vehicle','Environment','Creature','Accessory','Packaging','Paperwork'];

        $this->render('subject_index', [
            'title' => 'Meta / Subjects',
            'universes' => $universes,
            'types' => $types
        ]);
    }

    public function list(Request $request): void
    {
        $page = (int) $request->input('page', 1);
        $perPage = 20;
        $search = trim($request->input('q', ''));
        $type = trim($request->input('type', ''));
        $universeId = (int) $request->input('universe_id', 0);

        $data = Subject::getPaginatedWithDetails(
            $page, 
            $perPage, 
            $search, 
            $type,
            $universeId
        );

        $this->renderPartial('subject_list', [
            'subjects' => $data['items'],
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

        $universeId = (int) $request->input('universe_id');
        if ($universeId <= 0) {
            $this->json(['field' => 'universe_id', 'message' => 'Please select a universe'], 422);
            return;
        }

        // ENUM VALIDATION
        $allowedTypes = ['Character','Vehicle','Environment','Creature','Accessory','Packaging','Paperwork'];
        $type = $request->input('type', 'Character');
        if (!in_array($type, $allowedTypes)) {
            $this->json(['field' => 'type', 'message' => 'Invalid type selected.'], 422);
            return;
        }

        $slug = Subject::validateUniqueSlug($request->input('slug'), $name);
        if ($slug === null) {
            $this->json(['field' => 'slug', 'message' => 'This slug is already in use.'], 422);
            return;
        }

        Subject::create([
            'name' => $name,
            'slug' => $slug,
            'type' => $type,
            'universe_id' => $universeId,
            'description' => trim($request->input('description', ''))
        ]);

        $this->json(['success' => true]);
    }

    public function update(Request $request, int $id): void
    {
        if (!Subject::find($id)) {
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
        $allowedTypes = ['Character','Vehicle','Environment','Creature','Accessory','Packaging','Paperwork'];
        $type = $request->input('type', 'Character');
        if (!in_array($type, $allowedTypes)) {
            $this->json(['field' => 'type', 'message' => 'Invalid type selected.'], 422);
            return;
        }

        $slug = Subject::validateUniqueSlug($request->input('slug'), $name, $id);
        if ($slug === null) {
            $this->json(['field' => 'slug', 'message' => 'This slug is already in use.'], 422);
            return;
        }

        Subject::update($id, [
            'name' => $name,
            'slug' => $slug,
            'type' => $type,
            'universe_id' => (int) $request->input('universe_id'),
            'description' => trim($request->input('description', ''))
        ]);

        $db = Database::getInstance();
        $sql = "SELECT s.*, u.name as universe_name FROM meta_subjects s LEFT JOIN meta_universes u ON s.universe_id = u.id WHERE s.id = ?";
        $updated = $db->query($sql, [$id])->fetch(\PDO::FETCH_ASSOC);

        ob_start();
        $this->renderPartial('subject_row', ['s' => $updated]);
        $this->json(['success' => true, 'row_html' => ob_get_clean()]);
    }

    public function destroy(Request $request, int $id): void
    {
        // 1. Check Dependencies (CatalogToyItems)
        // We use class_exists check to prevent crash if model isn't created yet
        $itemCount = 0;
        if (class_exists(CatalogToyItem::class)) {
            $itemCount = CatalogToyItem::countBySubject($id);
        }
        
        // 2. Validate Migration
        $migrateTo = (int) $request->input('migrate_to', 0);

        if ($migrateTo > 0) {
            if ($migrateTo === $id) {
                $this->json(['error' => 'Cannot migrate items to the subject being deleted.'], 400);
                return;
            }
            if (!Subject::find($migrateTo)) {
                $this->json(['error' => 'The selected destination does not exist.'], 400);
                return;
            }
        }

        // 3. Conflict (409)
        if ($itemCount > 0 && $migrateTo === 0) {
            $this->json([
                'requires_migration' => true,
                'message' => "This subject is linked to {$itemCount} catalog item(s). Please reassign them.",
                'options_url' => "subject/migrate-on-delete-options?exclude={$id}"
            ], 409);
            return;
        }

        $db = Database::getInstance();

        try {
            $db->beginTransaction();

            // 4. Migrate
            if ($itemCount > 0 && $migrateTo > 0) {
                CatalogToyItem::migrateSubject($id, $migrateTo);
            }

            Subject::delete($id);

            Subject::delete($id);
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
        
        $sql = "SELECT id, name FROM meta_subjects";
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
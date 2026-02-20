<?php
namespace App\Modules\Media\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Kernel\Database\Database;
use App\Modules\Media\Models\MediaTag;

class MediaTagController extends Controller
{
    public function index(Request $request): void
    {
         $this->render('media_tag_index', [
            'title' => 'Media Tags'
        ]);
    }
    
    public function list(Request $request): void
    {
        $page = (int) $request->input('page', 1);
        $perPage = 20;
        $search = trim($request->input('q', ''));

        $data = MediaTag::getPaginatedWithUsageCount($page, $perPage, $search);

        $this->renderPartial('media_tag_list', [
            'tags' => $data['items'],
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

        if (mb_strlen($name) > 50) {
            $this->json(['field' => 'name', 'message' => 'Name cannot exceed 50 characters'], 422);
            return;
        }

        $slug = MediaTag::validateUniqueSlug($request->input('slug'), $name, 0);
        
        if ($slug === null) {
            $this->json(['field' => 'slug', 'message' => 'This slug is already in use.'], 422);
            return;
        }

        MediaTag::create([
            'name' => $name,
            'slug' => $slug
        ]);

        $this->json(['success' => true]);
    }

    public function update(Request $request, int $id): void
    {
        $existing = MediaTag::find($id);
        if (!$existing) {
            $this->json(['error' => 'Tag not found'], 404);
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

        $slug = MediaTag::validateUniqueSlug($request->input('slug'), $name, $id);

        if ($slug === null) {
            $this->json(['field' => 'slug', 'message' => 'This slug is already in use.'], 422);
            return;
        }

        MediaTag::update($id, [
            'name' => $name,
            'slug' => $slug
        ]);

        $updatedItem = MediaTag::find($id);
        
        $db = Database::getInstance();
        $updatedItem['usage_count'] = $db->query(
            "SELECT COUNT(*) FROM media_file_tags WHERE media_tag_id = ?", 
            [$id]
        )->fetchColumn();

        ob_start();
        $this->renderPartial('media_tag_row', ['t' => $updatedItem]);
        $html = ob_get_clean();

        $this->json([
            'success'  => true, 
            'row_html' => $html
        ]);
    }

    public function destroy(Request $request, int $id): void
    {
        if (!MediaTag::find($id)) {
            $this->json(['error' => 'Tag not found'], 404);
            return;
        }

        // 1. Check for dependencies (Files linked to this tag)
        $fileCount = MediaTag::countFiles($id);
        
        // 2. Validate Migration Request
        $migrateTo = (int) $request->input('migrate_to', 0);

        if ($migrateTo > 0) {
            if ($migrateTo === $id) {
                $this->json(['error' => 'Cannot migrate items to the tag being deleted.'], 400);
                return;
            }
            $target = MediaTag::find($migrateTo);
            if (!$target) {
                $this->json(['error' => 'The selected tag does not exist.'], 400);
                return;
            }
        }

        // 3. Handle Migration Requirement (409 Conflict)
        if ($fileCount > 0 && $migrateTo === 0) {
            $this->json([
                'requires_migration' => true,
                'message' => "This tag is linked to {$fileCount} media file(s). Please reassign them to another tag before deleting.",
                'options_url' => "media-tag/migrate-on-delete-options?exclude={$id}"
            ], 409);
            return;
        }

        $db = Database::getInstance();

        try {
            $db->beginTransaction();

            // 4. Migrate items if requested
            if ($fileCount > 0 && $migrateTo > 0) {
                MediaTag::migrateTag($id, $migrateTo);
            }

            // 5. Delete tag (Any remaining pivot table links will be handled by ON DELETE CASCADE if set up, or the cleanup in migrateTag)
            MediaTag::delete($id);

            $db->commit();
            $this->json(['success' => true]);

        } catch (\Exception $e) {
            $db->rollBack();
            error_log('Delete failed: ' . $e->getMessage());
            $this->json(['error' => 'Failed to delete record. Please try again.'], 500);
        }
    }

    public function migrateOnDeleteOptions(Request $request): void
    {
        $exclude = (int) $request->input('exclude', 0);
        $db = Database::getInstance();
        
        $sql = "SELECT id, name FROM media_tags";
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
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

        // ON DELETE CASCADE will remove all links automatically
        MediaTag::delete($id);
        $this->json(['success' => true]);
    }
}
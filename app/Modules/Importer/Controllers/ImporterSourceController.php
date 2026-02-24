<?php
namespace App\Modules\Importer\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Kernel\Database\Database;
use App\Modules\Importer\Models\ImporterSource;
use App\Modules\Importer\Models\ImporterItem;

class ImporterSourceController extends Controller
{
    public function index(Request $request): void
    {
        $this->render('importer_source_index', [
            'title' => 'Import Sources',
        ]);
    }

    public function list(Request $request): void
    {
        $page = (int) $request->input('page', 1);
        $search = trim($request->input('q', ''));
        $active = trim($request->input('active', ''));

        $data = ImporterSource::getPaginated($page, 20, $search, $active);

        $this->renderPartial('importer_source_list', [
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
        $baseUrl = trim($request->input('base_url', ''));
        $driverClass = trim($request->input('driver_class', ''));

        if ($name === '') {
            $this->json(['field' => 'name', 'message' => 'Name is required'], 422);
            return;
        }
        if ($baseUrl === '') {
            $this->json(['field' => 'base_url', 'message' => 'Base URL is required'], 422);
            return;
        }
        if ($driverClass === '') {
            $this->json(['field' => 'driver_class', 'message' => 'Driver class is required'], 422);
            return;
        }

        $slug = ImporterSource::validateUniqueSlug($request->input('slug'), $name, 0);
        if ($slug === null) {
            $this->json(['field' => 'slug', 'message' => 'This slug is already in use.'], 422);
            return;
        }

        $isActive = filter_var($request->input('is_active', true), FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

        ImporterSource::create([
            'name' => $name,
            'slug' => $slug,
            'base_url' => $baseUrl,
            'driver_class' => $driverClass,
            'is_active' => $isActive,
        ]);

        $this->json(['success' => true]);
    }

    public function update(Request $request, int $id): void
    {
        $existing = ImporterSource::find($id);
        if (!$existing) {
            $this->json(['error' => 'Source not found'], 404);
            return;
        }

        $name = trim($request->input('name', ''));
        $baseUrl = trim($request->input('base_url', ''));
        $driverClass = trim($request->input('driver_class', ''));

        if ($name === '') {
            $this->json(['field' => 'name', 'message' => 'Name is required'], 422);
            return;
        }
        if ($baseUrl === '') {
            $this->json(['field' => 'base_url', 'message' => 'Base URL is required'], 422);
            return;
        }
        if ($driverClass === '') {
            $this->json(['field' => 'driver_class', 'message' => 'Driver class is required'], 422);
            return;
        }

        $slug = ImporterSource::validateUniqueSlug($request->input('slug'), $name, $id);
        if ($slug === null) {
            $this->json(['field' => 'slug', 'message' => 'This slug is already in use.'], 422);
            return;
        }

        $isActive = filter_var($request->input('is_active', true), FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

        ImporterSource::update($id, [
            'name' => $name,
            'slug' => $slug,
            'base_url' => $baseUrl,
            'driver_class' => $driverClass,
            'is_active' => $isActive,
        ]);

        $updated = ImporterSource::find($id);

        $db = Database::getInstance();
        $updated['item_count'] = (int) $db->query(
            "SELECT COUNT(*) FROM importer_items WHERE source_id = ?",
            [$id]
        )->fetchColumn();
        $updated['last_activity'] = $db->query(
            "SELECT MAX(last_imported_at) FROM importer_items WHERE source_id = ?",
            [$id]
        )->fetchColumn();

        ob_start();
        $this->renderPartial('importer_source_row', ['s' => $updated]);
        $html = ob_get_clean();

        $this->json(['success' => true, 'row_html' => $html]);
    }

    public function destroy(Request $request, int $id): void
    {
        $existing = ImporterSource::find($id);
        if (!$existing) {
            $this->json(['error' => 'Source not found'], 404);
            return;
        }

        $itemCount = ImporterItem::count('source_id', $id);

        if ($itemCount > 0) {
            $this->json([
                'error' => "Cannot delete: this source has {$itemCount} imported item(s). Remove them first, or deactivate the source instead."
            ], 409);
            return;
        }

        ImporterSource::delete($id);
        $this->json(['success' => true]);
    }
}

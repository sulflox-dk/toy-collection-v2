<?php
namespace App\Modules\Meta\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Modules\Meta\Models\Manufacturer;
use App\Kernel\Database\Database; // Needed for the uniqueness check

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
        $perPage = 2;
        $offset = ($page - 1) * $perPage;
        $search = trim($request->input('q', ''));

        $db = Database::getInstance();

        // 1. Build Query
        $sql = "SELECT * FROM meta_manufacturers";
        $params = [];

        if ($search) {
            $sql .= " WHERE name LIKE ?";
            $params[] = "%$search%";
        }

        // 2. Count Total
        $countSql = str_replace('SELECT *', 'SELECT COUNT(*)', $sql);
        $total = $db->query($countSql, $params)->fetchColumn();
        $totalPages = ceil($total / $perPage);

        // 3. Fetch Data
        $sql .= " ORDER BY name ASC LIMIT $perPage OFFSET $offset";
        $manufacturers = $db->query($sql, $params)->fetchAll();

        // 4. Render Partial with Pagination Data
        $this->renderPartial('manufacturer_list', [
            'manufacturers' => $manufacturers,
            'pagination' => [
                'current' => $page,
                'total'   => $totalPages,
                'count'   => $total
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
     * Delete a manufacturer.
     * DELETE /manufacturer/{id}
     */
    public function destroy(Request $request, string $id): void
    {
        Manufacturer::delete((int) $id);
        $this->json(['success' => true]);
    }
}
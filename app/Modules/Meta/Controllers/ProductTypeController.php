<?php
namespace App\Modules\Meta\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Kernel\Database\Database;
use App\Modules\Meta\Models\ProductType;
use App\Modules\Catalog\Models\CatalogToy;

class ProductTypeController extends Controller
{
    public function index(Request $request): void
    {
        $this->render('product_type_index', [
            'title' => 'Product Types'
        ]);
    }

    public function list(Request $request): void
    {
        $page = (int) $request->input('page', 1);
        $perPage = 20;
        $search = trim($request->input('q', ''));

        $data = ProductType::getPaginated($page, $perPage, $search);

        $this->renderPartial('product_type_list', [
            'types' => $data['items'],
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

        $slug = ProductType::validateUniqueSlug($request->input('slug'), $name);
        if ($slug === null) {
            $this->json(['field' => 'slug', 'message' => 'This slug is already in use.'], 422);
            return;
        }

        ProductType::create([
            'name' => $name,
            'slug' => $slug,
            'description' => trim($request->input('description', ''))
        ]);

        $this->json(['success' => true]);
    }

    public function update(Request $request, int $id): void
    {
        if (!ProductType::find($id)) {
            $this->json(['error' => 'Record not found'], 404);
            return;
        }

        $name = trim($request->input('name', ''));
        $slug = ProductType::validateUniqueSlug($request->input('slug'), $name, $id);
        
        if ($slug === null) {
            $this->json(['field' => 'slug', 'message' => 'This slug is already in use.'], 422);
            return;
        }

        ProductType::update($id, [
            'name' => $name,
            'slug' => $slug,
            'description' => trim($request->input('description', ''))
        ]);

        $updated = ProductType::find($id);
        ob_start();
        $this->renderPartial('product_type_row', ['type' => $updated]);
        
        $this->json(['success' => true, 'row_html' => ob_get_clean()]);
    }

    public function destroy(Request $request, int $id): void
    {
        // 1. Check Dependencies
        $toyCount = CatalogToy::countByProductType($id);
        
        // 2. Validate Migration
        $migrateTo = (int) $request->input('migrate_to', 0);

        if ($migrateTo > 0) {
            if ($migrateTo === $id) {
                $this->json(['error' => 'Cannot migrate items to the type being deleted.'], 400);
                return;
            }
            if (!ProductType::find($migrateTo)) {
                $this->json(['error' => 'The selected destination does not exist.'], 400);
                return;
            }
        }

        // 3. Conflict (409)
        if ($toyCount > 0 && $migrateTo === 0) {
            $this->json([
                'requires_migration' => true,
                'message' => "This product type is used by {$toyCount} catalog toy(s). Please reassign them.",
                'options_url' => "product-type/migrate-on-delete-options?exclude={$id}"
            ], 409);
            return;
        }

        $db = Database::getInstance();

        try {
            $db->beginTransaction();

            // 4. Migrate if requested
            if ($toyCount > 0 && $migrateTo > 0) {
                CatalogToy::migrateProductType($id, $migrateTo);
            }

            ProductType::delete($id);

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
        
        $sql = "SELECT id, name FROM meta_product_types";
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
<?php
namespace App\Modules\Meta\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Modules\Meta\Models\Manufacturer;

class ManufacturerController extends Controller
{
    public function index(Request $request): void
    {
        $manufacturers = Manufacturer::all();

        // Seed defaults when the table is empty
        if (empty($manufacturers)) {
            Manufacturer::create(['name' => 'Kenner', 'slug' => 'kenner']);
            Manufacturer::create(['name' => 'Hasbro', 'slug' => 'hasbro']);
            $manufacturers = Manufacturer::all();
        }

        $this->render('manufacturer_index', [
            'title'         => 'Manufacturers',
            'manufacturers' => $manufacturers,
        ]);
    }

    public function show(Request $request, string $id): void
    {
        $manufacturer = Manufacturer::find((int) $id);

        if (!$manufacturer) {
            $this->abort(404, 'Manufacturer not found');
            return;
        }

        $this->render('manufacturer_show', [
            'title'        => $manufacturer['name'],
            'manufacturer' => $manufacturer,
        ]);
    }

    /**
     * Returns the HTML Table of manufacturers (for AJAX)
     * GET /manufacturer/list
     */
    public function list(): void
    {
        $manufacturers = Manufacturer::all();

        $this->renderPartial('partials/manufacturer_list', [
            'manufacturers' => $manufacturers,
        ]);
    }

    /**
     * Store a new manufacturer or update an existing one.
     * POST /manufacturer      (create)
     * POST /manufacturer/{id} (update via _method=PUT)
     */
    public function store(Request $request, ?string $id = null): void
    {
        $name = trim($request->input('name', ''));
        if ($name === '') {
            $this->json(['error' => 'Name is required'], 422);
            return;
        }

        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
        $showOnDashboard = $request->input('show_on_dashboard') ? 1 : 0;

        $data = [
            'name' => $name,
            'slug' => $slug,
            'show_on_dashboard' => $showOnDashboard,
        ];

        if ($id) {
            Manufacturer::update((int) $id, $data);
        } else {
            Manufacturer::create($data);
        }

        $this->json(['success' => true]);
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

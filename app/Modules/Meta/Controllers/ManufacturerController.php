<?php
namespace App\Modules\Meta\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Kernel\Database\Database;
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
    public function list()
    {
        $db = Database::getInstance();
        
        // Simple query (we can add pagination/search later)
        $manufacturers = $db->query("
            SELECT * FROM manufacturers 
            ORDER BY name ASC
        ")->fetchAll();

        // Render ONLY the partial view
        // Note: We don't use the main layout here, just the fragment
        echo $this->render('Meta/Views/partials/manufacturer_list', [
            'manufacturers' => $manufacturers
        ]);
    }
}

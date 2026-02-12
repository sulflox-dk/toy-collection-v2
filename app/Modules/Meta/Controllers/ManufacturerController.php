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
}

<?php
namespace App\Modules\Collection\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Kernel\Database\Database;
use App\Modules\Collection\Models\CollectionToy;

class CollectionToyController extends Controller
{
    public function index(Request $request): void
    {
        $db = Database::getInstance();
        
        $universes = $db->query("SELECT id, name FROM meta_universes ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $toyLines = $db->query("SELECT id, name FROM meta_toy_lines ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $statuses = $db->query("SELECT id, name FROM meta_acquisition_statuses ORDER BY sort_order ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $storageUnits = $db->query("SELECT id, name, box_code FROM collection_storage_units ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
        
        // --- ADD THESE TWO NEW LOOKUPS ---
        $manufacturers = $db->query("SELECT id, name FROM meta_manufacturers ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $productTypes = $db->query("SELECT id, name FROM meta_product_types ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);

        $this->render('collection_toy_index', [
            'title' => 'My Collection',
            'universes' => $universes,
            'toyLines' => $toyLines,
            'statuses' => $statuses,
            'storageUnits' => $storageUnits,
            'manufacturers' => $manufacturers, // Pass to view
            'productTypes' => $productTypes,   // Pass to view
            'scripts' => [
                'assets/js/modules/collection/collection_toys.js'
            ]
        ]);
    }

    public function list(Request $request): void
    {
        $page = (int) $request->input('page', 1);
        
        $search = trim($request->input('q', ''));
        $universeId = (int) $request->input('universe_id', 0);
        $toyLineId = (int) $request->input('toy_line_id', 0);
        $acquisitionStatusId = (int) $request->input('acquisition_status_id', 0);
        $storageUnitId = (int) $request->input('storage_unit_id', 0);
        
        // --- ADD THESE NEW INPUTS ---
        $manufacturerId = (int) $request->input('manufacturer_id', 0);
        $productTypeId = (int) $request->input('product_type_id', 0);
        $missingParts = trim($request->input('missing_parts', ''));
        $imageStatus = trim($request->input('image_status', ''));
        
        $viewMode = trim($request->input('view', '')); 
        if ($viewMode === '') {
            $viewMode = $_COOKIE['collection-toy_view'] ?? 'cards'; 
        }
        if (!in_array($viewMode, ['list', 'cards'])) {
            $viewMode = 'cards';
        }

        $perPage = ($viewMode === 'cards') ? 24 : 20;

        $data = CollectionToy::getPaginatedWithDetails(
            $page, $perPage, $search, 
            $universeId, $toyLineId, $acquisitionStatusId, $storageUnitId,
            $manufacturerId, $productTypeId, $missingParts, $imageStatus // <-- Pass them to the model
        );

        $this->renderPartial('collection_toy_list', [
            'collectionToys' => $data['items'],
            'viewMode' => $viewMode,
            'pagination' => [
                'current' => $page,
                'total'   => $data['totalPages'],
                'count'   => $data['total']
            ]
        ]);
    }
}
<?php
namespace App\Modules\Importer\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Modules\Importer\Models\ImporterSource;

class ImporterGuideController extends Controller
{
    public function index(Request $request): void
    {
        $this->render('importer_guide_index', [
            'title' => 'Importer Guide',
            'sources' => ImporterSource::allActive(),
        ]);
    }
}

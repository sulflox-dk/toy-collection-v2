<?php
namespace App\Modules\Importer\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Modules\Importer\Models\ImporterSource;

class ImporterGuideController extends Controller
{
    public function index(Request $request): void
    {
        $activeSources = ImporterSource::allActive();

        $this->render('importer_guide_index', [
            'title' => 'Importer Guide',
            'sources' => $activeSources,
            'maxSourcesPerGroup' => max(1, count($activeSources)),
        ]);
    }
}

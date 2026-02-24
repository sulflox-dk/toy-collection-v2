<?php
namespace App\Modules\Importer\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Kernel\Database\Database;
use App\Modules\Importer\Models\ImporterLog;
use App\Modules\Importer\Models\ImporterSource;

class ImporterLogController extends Controller
{
    public function index(Request $request): void
    {
        $sources = ImporterSource::allActive();

        $this->render('importer_log_index', [
            'title'   => 'Import Logs',
            'sources' => $sources,
        ]);
    }

    public function list(Request $request): void
    {
        $page = (int) $request->input('page', 1);
        $search = trim($request->input('q', ''));
        $sourceId = (int) $request->input('source_id', 0);
        $status = trim($request->input('status', ''));

        $data = ImporterLog::getPaginated($page, 30, $search, $sourceId, $status);

        $this->renderPartial('importer_log_list', [
            'logs' => $data['items'],
            'pagination' => [
                'current' => $page,
                'total'   => $data['totalPages'],
                'count'   => $data['total']
            ]
        ]);
    }
}

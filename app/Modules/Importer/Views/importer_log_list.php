<?= $this->renderPartial('common/table_list', [
    'headers' => [
        'Time' => [
            'width' => '15%',
            'class' => 'ps-3'
        ],
        'Source' => [
            'width' => '15%',
            'class' => ''
        ],
        'Status' => [
            'width' => '10%',
            'class' => 'text-center'
        ],
        'Message' => [
            'width' => '40%',
            'class' => ''
        ],
        'External ID' => [
            'width' => '15%',
            'class' => ''
        ],
    ],
    'items' => $logs,
    'rowPartial' => '../../Modules/Importer/Views/importer_log_row',
    'itemKey' => 'log',
    'pagination' => $pagination,
    'emptyMessage' => 'No import logs found.',
    'emptyIcon' => 'fa-clipboard-list',
    'hideActions' => true
]) ?>

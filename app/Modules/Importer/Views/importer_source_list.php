<?= $this->renderPartial('common/table_list', [
    'headers' => [
        'Source' => [
            'width' => '25%',
            'class' => 'ps-3'
        ],
        'Base URL' => [
            'width' => '20%',
            'class' => ''
        ],
        'Driver' => [
            'width' => '15%',
            'class' => ''
        ],
        'Items' => [
            'width' => '10%',
            'class' => 'text-center'
        ],
        'Status' => [
            'width' => '10%',
            'class' => 'text-center'
        ],
    ],
    'items' => $sources,
    'rowPartial' => '../../Modules/Importer/Views/importer_source_row',
    'itemKey' => 's',
    'pagination' => $pagination,
    'emptyMessage' => 'No import sources found.',
    'emptyIcon' => 'fa-cloud-download-alt'
]) ?>

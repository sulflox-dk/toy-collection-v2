<?= $this->renderPartial('common/table_list', [
    'headers' => [
        'Name' => [
            'width' => '30%', 
            'class' => 'ps-3' // Left align + padding
        ], 
        'Dashboard' => [
            'width' => '20%', 
            'class' => 'text-center' // Center align
        ], 
        'Toy Lines' => [
            'width' => '20%', 
            'class' => 'text-center' // Center align
        ]
    ],
    'items' => $manufacturers,
    'rowPartial' => '../../Modules/Meta/Views/manufacturer_row',
    'itemKey' => 'm',
    'pagination' => $pagination,
    'emptyMessage' => 'No manufacturers found.',
    'emptyIcon' => 'fa-industry'
]) ?>
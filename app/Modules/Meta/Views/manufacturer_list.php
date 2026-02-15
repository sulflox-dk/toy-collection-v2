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
    'rowPartial' => 'manufacturer_row', // Or use the relative path if needed
    'itemKey' => 'm',
    'pagination' => $pagination,
    'emptyMessage' => 'No manufacturers found.',
    'emptyIcon' => 'fa-industry'
]) ?>
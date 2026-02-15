<?= $this->renderPartial('common/table_list', [
    'headers' => [
        'Name' =>         ['width' => '25%', 'class' => 'ps-3 fw-bold'], // Bold header? Sure!
        'Manufacturer' => ['width' => '20%', 'class' => ''],
        'Universe' =>     ['width' => '20%', 'class' => ''],
        'Status' =>       ['width' => '15%', 'class' => 'text-center']
    ],
    'items' => $toyLines,
    'rowPartial' => 'toy_line_row',
    'itemKey' => 't', // toy_line_row expects $t
    'pagination' => $pagination,
    'emptyMessage' => 'No toy lines found.',
    'emptyIcon' => 'fa-box-open'
]) ?>
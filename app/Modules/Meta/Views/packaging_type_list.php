<?= $this->renderPartial('common/table_list', [
    'headers' => [
        'Name / Slug' => ['width' => '30%', 'class' => 'ps-3'],
        'Description' => ['width' => '45%', 'class' => ''],
        'Sort Order'  => ['width' => '15%', 'class' => 'text-center'],
    ],
    'items' => $types,
    'rowPartial' => '../../Modules/Meta/Views/packaging_type_row',
    'itemKey' => 'type',
    'pagination' => $pagination,
    'emptyMessage' => 'No packaging types found.',
    'emptyIcon' => 'fa-box'
]) ?>
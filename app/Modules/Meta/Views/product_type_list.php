<?= $this->renderPartial('common/table_list', [
    'headers' => [
        'Name / Slug' => ['width' => '20%', 'class' => 'ps-3'],
        'Description' => ['width' => '70%', 'class' => '']
    ],
    'items' => $types,
    'rowPartial' => '../../Modules/Meta/Views/product_type_row',
    'itemKey' => 'type', 
    'pagination' => $pagination,
    'emptyMessage' => 'No product types found.',
    'emptyIcon' => 'fa-shapes'
]) ?>
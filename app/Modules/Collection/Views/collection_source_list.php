<?= $this->renderPartial('common/table_list', [
    'headers' => [
        'Name'    => ['width' => '40%', 'class' => 'fw-bold ps-3'],
        'Website' => ['width' => '40%', 'class' => '']
    ],
    'items' => $sources,
    'rowPartial' => '../../Modules/Collection/Views/collection_source_row',
    'itemKey' => 'source',
    'pagination' => $pagination,
    'emptyMessage' => 'No purchase sources found.',
    'emptyIcon' => 'fa-shop'
]) ?>

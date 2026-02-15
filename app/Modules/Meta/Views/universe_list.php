<?= $this->renderPartial('common/table_list', [
    'headers' => [
        'Name / Slug' => ['width' => '30%', 'class' => 'ps-3'],
        'Description' => ['width' => '25%', 'class' => ''],
        'Dashboard'   => ['width' => '15%', 'class' => 'text-center'],
        'Toy Lines'   => ['width' => '15%', 'class' => 'text-center']
    ],
    'items' => $universes,
    'rowPartial' => '../../Modules/Meta/Views/universe_row',
    'itemKey' => 'u',
    'pagination' => $pagination,
    'emptyMessage' => 'No universes found.',
    'emptyIcon' => 'fa-box-open'
]) ?>
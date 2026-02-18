<?= $this->renderPartial('common/table_list', [
    'headers' => [
        'Name / Slug' => ['width' => '50%', 'class' => 'ps-3'],
        'Sort Order'  => ['width' => '40%', 'class' => 'text-center'],
    ],
    'items' => $statuses,
    'rowPartial' => '../../Modules/Meta/Views/acquisition_status_row',
    'itemKey' => 'status',
    'pagination' => $pagination,
    'emptyMessage' => 'No statuses found.',
    'emptyIcon' => 'fa-clipboard-list'
]) ?>
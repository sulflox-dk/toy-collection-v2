<?= $this->renderPartial('common/table_list', [
    'headers' => [
        'Name / Email' => ['width' => '35%', 'class' => 'ps-3'],
        'Role'         => ['width' => '15%', 'class' => 'text-center'],
        'Created'      => ['width' => '20%', 'class' => ''],
    ],
    'items' => $users,
    'rowPartial' => '../../Modules/Auth/Views/user_row',
    'itemKey' => 'u',
    'pagination' => $pagination,
    'emptyMessage' => 'No users found.',
    'emptyIcon' => 'fa-users'
]) ?>

<?= $this->renderPartial('common/table_list', [
    'headers' => [
        'Name / Slug' => ['width' => '25%', 'class' => 'ps-3'],
        'Type'        => ['width' => '12%', 'class' => ''],
        'Universe'    => ['width' => '20%', 'class' => ''],
        'Description' => ['width' => '25%', 'class' => '']
    ],
    'items' => $subjects,
    'rowPartial' => '../../Modules/Meta/Views/subject_row',
    'itemKey' => 's',
    'pagination' => $pagination,
    'emptyMessage' => 'No subjects found.',
    'emptyIcon' => 'fa-users'
]) ?>
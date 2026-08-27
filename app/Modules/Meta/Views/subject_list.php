<?= $this->renderPartial('common/table_list', [
    'headers' => [
        'Photo'       => ['width' => '60px', 'class' => 'text-center'],
        'Name / Slug' => ['width' => '22%', 'class' => 'ps-3'],
        'Type'        => ['width' => '12%', 'class' => ''],
        'Universe'    => ['width' => '18%', 'class' => ''],
        'Description' => ['width' => '23%', 'class' => '']
    ],
    'items' => $subjects,
    'rowPartial' => '../../Modules/Meta/Views/subject_row',
    'itemKey' => 's',
    'pagination' => $pagination,
    'emptyMessage' => 'No subjects found.',
    'emptyIcon' => 'fa-users'
]) ?>
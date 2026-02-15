<?= $this->renderPartial('common/table_list', [
    'headers' => [
        'Name / Slug' => ['width' => '30%', 'class' => 'ps-3'],
        'Type / Year' => ['width' => '20%', 'class' => ''],
        'Universe'    => ['width' => '30%', 'class' => ''],
        'Dashboard'   => ['width' => '10%', 'class' => 'text-center']
    ],
    'items' => $sources,
    // We use the relative path to be safe, ensuring the Template engine finds it easily
    'rowPartial' => '../../Modules/Meta/Views/entertainment_source_row',
    'itemKey' => 's',
    'pagination' => $pagination,
    'emptyMessage' => 'No sources found.',
    'emptyIcon' => 'fa-film'
]) ?>
<?= $this->renderPartial('common/table_list', [
    'headers' => [
        'Name / Slug'  => ['width' => '25%', 'class' => 'ps-3'],
        'Abbreviation' => ['width' => '15%', 'class' => 'text-center'],
        'Description'  => ['width' => '30%', 'class' => 'text-muted'],
        'Sort Order'   => ['width' => '10%', 'class' => 'text-center'],
    ],
    'items' => $grades,
    'rowPartial' => '../../Modules/Meta/Views/condition_grade_row',
    'itemKey' => 'g',
    'pagination' => $pagination,
    'emptyMessage' => 'No grades found.',
    'emptyIcon' => 'fa-star-half-stroke'
]) ?>
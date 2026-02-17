<?= $this->renderPartial('common/table_list', [
    'headers' => [
        'Name / Slug' => ['width' => '50%', 'class' => 'ps-3'],
        'Website'     => ['width' => '50%', 'class' => ''],
    ],
    'items' => $companies,
    'rowPartial' => '../../Modules/Meta/Views/grading_company_row',
    'itemKey' => 'company',
    'pagination' => $pagination,
    'emptyMessage' => 'No grading companies found.',
    'emptyIcon' => 'fa-building'
]) ?>
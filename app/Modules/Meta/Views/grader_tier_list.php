<?= $this->renderPartial('common/table_list', [
    'headers' => [
        'Name / Slug' => ['width' => '50%', 'class' => 'ps-3'],
        'Sort Order'  => ['width' => '40%', 'class' => 'text-center'],
    ],
    'items' => $tiers,
    'rowPartial' => '../../Modules/Meta/Views/grader_tier_row',
    'itemKey' => 'tier',
    'pagination' => $pagination,
    'emptyMessage' => 'No grader tiers found.',
    'emptyIcon' => 'fa-award'
]) ?>
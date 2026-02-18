<?= $this->renderPartial('common/table_list', [
    'headers' => [
        'Name' => [
            'width' => '50%', 
            'class' => 'ps-3' 
        ], 
        'Usage Count' => [
            'width' => '40%', 
            'class' => 'text-center'
        ]
    ],
    'items' => $tags,
    'rowPartial' => '../../Modules/Media/Views/media_tag_row',
    'itemKey' => 't',
    'pagination' => $pagination,
    'emptyMessage' => 'No tags found.',
    'emptyIcon' => 'fa-tags'
]) ?>
<?= $this->renderPartial('common/table_list', [
    'headers' => [
        'Box Code'    => ['width' => '15%', 'class' => 'ps-3'],
        'Name'        => ['width' => '30%', 'class' => 'fw-bold'],
        'Location'    => ['width' => '25%', 'class' => ''],
        'Description' => ['width' => '30%', 'class' => '']
    ],
    'items' => $storageUnits,
    'rowPartial' => '../../Modules/Collection/Views/storage_unit_row',
    'itemKey' => 'su',
    'pagination' => $pagination,
    'emptyMessage' => 'No storage units found.',
    'emptyIcon' => 'fa-boxes-stacked'
]) ?>
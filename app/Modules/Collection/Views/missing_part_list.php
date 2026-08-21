<?= $this->renderPartial('common/table_list', [
    'headers' => [
        ''            => ['width' => '5%', 'class' => 'ps-3'],
        'Part'        => ['width' => '25%', 'class' => 'fw-bold'],
        'Toy'         => ['width' => '20%', 'class' => ''],
        'Universe'    => ['width' => '12%', 'class' => ''],
        'Manufacturer / Line' => ['width' => '18%', 'class' => ''],
        'Year / Wave' => ['width' => '10%', 'class' => ''],
        'Cherish'     => ['width' => '10%', 'class' => 'text-nowrap']
    ],
    'items' => $parts,
    'rowPartial' => '../../Modules/Collection/Views/missing_part_row',
    'itemKey' => 'p',
    'pagination' => $pagination,
    'emptyMessage' => 'No missing parts found — everything you own is complete.',
    'emptyIcon' => 'fa-circle-check'
]) ?>

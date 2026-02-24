<?php if ($viewMode === 'cards'): ?>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4 mb-4">
        <?php foreach($collectionToys as $t): ?>
            <?= $this->renderPartial('collection_toy_card', ['t' => $t]) ?>
        <?php endforeach; ?>
    </div>

    <?php if (empty($collectionToys)): ?>
        <div class="text-center py-5 text-muted bg-white border rounded shadow-sm mb-4">
            <i class="fa-solid fa-box-open fa-3x mb-3 opacity-50"></i>
            <p class="mb-0 fs-5">Your collection is empty or no toys match your filters.</p>
        </div>
    <?php endif; ?>

    <?php if(isset($pagination)) echo $this->renderPartial('common/pagination', ['pagination' => $pagination]); ?>

<?php else: ?>

    <?= $this->renderPartial('common/table_list', [
        'headers' => [
            'Image' =>               ['width' => '60px', 'class' => 'ps-3 text-center'],
            'Toy Name' =>            ['width' => '25%', 'class' => 'fw-bold'],
            'Universe / Line' =>     ['width' => '20%', 'class' => ''],
            'Status / Condition' =>  ['width' => '15%', 'class' => ''],
            'Storage Unit' =>        ['width' => '20%', 'class' => ''],
        ],
        'items' => $collectionToys,
        'rowPartial' => 'collection_toy_row',
        'itemKey' => 't',
        'pagination' => $pagination,
        'emptyMessage' => 'Your collection is empty.',
        'emptyIcon' => 'fa-box-open'
    ]) ?>

<?php endif; ?>
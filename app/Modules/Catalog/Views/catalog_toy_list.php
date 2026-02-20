<?php if ($viewMode === 'cards'): ?>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4 mb-4">
        <?php foreach($catalogToys as $t): ?>
            <?= $this->renderPartial('catalog_toy_card', ['t' => $t]) ?>
        <?php endforeach; ?>
    </div>

    <?php if (empty($catalogToys)): ?>
        <div class="text-center py-5 text-muted bg-white border rounded shadow-sm mb-4">
            <i class="fa-solid fa-robot fa-3x mb-3 opacity-50"></i>
            <p class="mb-0 fs-5">No catalog toys found matching your filters.</p>
        </div>
    <?php endif; ?>

    <?php 
    if(isset($pagination)) {
        echo $this->renderPartial('common/pagination', ['pagination' => $pagination]); 
    }
    ?>

<?php else: ?>

<?= $this->renderPartial('common/table_list', [
    'headers' => [
        'Image' =>               ['width' => '60px', 'class' => 'ps-3 text-center'],
        'Name / Type' =>                ['width' => '25%', 'class' => 'fw-bold'],
        'Universe / Source' =>   ['width' => '20%', 'class' => ''],
        'Manufacturer / Line' => ['width' => '15%', 'class' => ''],
        'Year / Wave' =>         ['width' => '15%', 'class' => ''],
        'Collection' =>          ['width' => '15%', 'class' => 'text-center'],
    ],
    'items' => $catalogToys,
    'rowPartial' => 'catalog_toy_row',
    'itemKey' => 't',
    'pagination' => $pagination,
    'emptyMessage' => 'No catalog toys found matching your filters.',
    'emptyIcon' => 'fa-robot'
]) ?>

<?php endif; ?>
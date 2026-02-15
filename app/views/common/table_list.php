<?php
// Params:
// $headers: ['Name' => ['width' => '30%', 'class' => 'ps-3'], 'Status' => '10%']
// ... (rest same as before)
?>

<div class="table-responsive">
    <table class="table table-hover align-middle mb-0 border">
        <thead class="bg-light">
            <tr>
                <?php foreach($headers as $label => $config): ?>
                    <?php 
                        // Determine Width and Class based on input type
                        if (is_array($config)) {
                            $width = $config['width'] ?? 'auto';
                            $class = $config['class'] ?? '';
                        } else {
                            // Backward compatibility for simple string '30%'
                            $width = $config;
                            $class = ''; 
                        }
                    ?>
                    <th style="width:<?= $e($width) ?>" class="<?= $e($class) ?>">
                        <?= $e($label) ?>
                    </th>
                <?php endforeach; ?>
                <th class="text-end pe-3"></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
                <tr>
                    <td colspan="<?= count($headers) + 1 ?>" class="text-center py-5 text-muted">
                        <i class=\"fa-solid <?= $e($emptyIcon ?? 'fa-box-open') ?> fa-2x mb-2\"></i>
                        <p class=\"mb-0\"><?= $e($emptyMessage ?? 'No items found.') ?></p>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach($items as $item): ?>
                    <?= $this->renderPartial($rowPartial, [$itemKey => $item]) ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php 
if(isset($pagination)) {
    echo $this->renderPartial('common/pagination', ['pagination' => $pagination]); 
}
?>
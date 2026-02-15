<?php if (isset($pagination) && $pagination['total'] > 1): ?>
<div class="p-3 border-top bg-light d-flex justify-content-between align-items-center">
    <small class="text-muted">
        Showing page <?= $e($pagination['current']) ?> of <?= $e($pagination['total']) ?> 
        (<?= $e($pagination['count']) ?> items)
    </small>
    <nav aria-label="Page navigation">
        <ul class="pagination pagination-sm mb-0">
            <li class="page-item <?= $pagination['current'] <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="#" data-page="<?= $e($pagination['current'] - 1) ?>">Previous</a>
            </li>

            <?php for($i = 1; $i <= $pagination['total']; $i++): ?>
                <li class="page-item <?= $i == $pagination['current'] ? 'active' : '' ?>">
                    <a class="page-link" href="#" data-page="<?= $e($i) ?>"><?= $e($i) ?></a>
                </li>
            <?php endfor; ?>

            <li class="page-item <?= $pagination['current'] >= $pagination['total'] ? 'disabled' : '' ?>">
                <a class="page-link" href="#" data-page="<?= $e($pagination['current'] + 1) ?>">Next</a>
            </li>
        </ul>
    </nav>
</div>
<?php endif; ?>
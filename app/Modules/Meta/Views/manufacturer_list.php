<div class="table-responsive">
    <table class="table table-hover align-middle mb-0 border">
        <thead class="bg-light">
            <tr>
                <th class="ps-3">Name</th>
                <th>Slug</th>
                <th class="text-center">Dashboard</th>
                <th class="text-center">Toy Lines</th>
                <th class="text-end pe-3"></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($manufacturers)): ?>
                <tr>
                    <td colspan="4" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-box-open fa-2x mb-2"></i>
                        <p class="mb-0">No manufacturers found.</p>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach($manufacturers as $m): ?>
                    <?= $this->renderPartial('manufacturer_row', ['m' => $m]); ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (isset($pagination) && $pagination['total'] > 1): ?>
<div class="p-3 border-top bg-light d-flex justify-content-between align-items-center">
    <small class="text-muted">
        Showing page <?= $pagination['current'] ?> of <?= $pagination['total'] ?> 
        (<?= $pagination['count'] ?> items)
    </small>
    <nav aria-label="Page navigation">
        <ul class="pagination pagination-sm mb-0">
            <li class="page-item <?= $pagination['current'] <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="#" data-page="<?= $pagination['current'] - 1 ?>">Previous</a>
            </li>

            <?php for($i = 1; $i <= $pagination['total']; $i++): ?>
                <li class="page-item <?= $i == $pagination['current'] ? 'active' : '' ?>">
                    <a class="page-link" href="#" data-page="<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <li class="page-item <?= $pagination['current'] >= $pagination['total'] ? 'disabled' : '' ?>">
                <a class="page-link" href="#" data-page="<?= $pagination['current'] + 1 ?>">Next</a>
            </li>
        </ul>
    </nav>
</div>
<?php endif; ?>
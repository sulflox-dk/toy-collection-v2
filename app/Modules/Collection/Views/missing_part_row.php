<tr data-id="<?= $e($p['catalog_toy_item_id']) ?>">
    <td class="ps-3">
        <div class="ratio ratio-1x1 bg-light rounded" style="width: 40px;">
            <?php if (!empty($p['image_path'])): ?>
                <img src="<?= $e($p['image_path']) ?>" class="object-fit-cover rounded" alt="">
            <?php else: ?>
                <div class="d-flex align-items-center justify-content-center text-muted">
                    <i class="fa-solid fa-cube"></i>
                </div>
            <?php endif; ?>
        </div>
    </td>
    <td>
        <div class="fw-bold"><?= $e($p['part_name']) ?></div>
        <div>
            <span class="badge bg-secondary"><?= $e($p['part_type'] ?: 'Part') ?></span>
            <?php if ($p['problem_status'] === 'repro'): ?>
                <span class="badge bg-warning text-dark">Reproduction</span>
            <?php else: ?>
                <span class="badge bg-danger">Missing</span>
            <?php endif; ?>
        </div>
    </td>
    <td>
        <div><?= $e($p['toy_name']) ?></div>
        <div class="text-muted small"><?= $e($p['product_type_name'] ?: '') ?></div>
    </td>
    <td><?= $e($p['universe_name'] ?: '—') ?></td>
    <td>
        <div><?= $e($p['manufacturer_name'] ?: '—') ?></div>
        <div class="text-muted small"><?= $e($p['toy_line_name'] ?: '') ?></div>
    </td>
    <td>
        <?= $e($p['year_released'] ?: '—') ?>
        <?php if (!empty($p['wave'])): ?>
            <div class="text-muted small"><?= $e($p['wave']) ?></div>
        <?php endif; ?>
    </td>
    <td class="text-nowrap">
        <?php $rating = (int) ($p['cherish_rating'] ?? 0); ?>
        <?php if ($rating > 0): ?>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <i class="fa-solid fa-star <?= $i <= $rating ? 'text-warning' : 'text-muted opacity-25' ?>" style="font-size: 0.75rem;"></i>
            <?php endfor; ?>
        <?php else: ?>
            <span class="text-muted small">Unrated</span>
        <?php endif; ?>
    </td>
    <td class="text-end pe-3">
        <button type="button" class="btn btn-sm btn-outline-secondary"
                onclick="CollectionWizard.editToy(<?= $e($p['collection_toy_id']) ?>)"
                title="Edit Collection Details">
            <i class="fa-solid fa-pencil"></i>
        </button>
    </td>
</tr>

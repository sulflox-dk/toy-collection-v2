<tr data-id="<?= $e($t['id']) ?>" class="align-middle">
    <td class="ps-3 text-center">
        <?php if(!empty($t['image_path'])): ?>
            <img src="<?= $e($t['image_path']) ?>" alt="Thumbnail" class="img-thumbnail entity-row-thumb">
        <?php else: ?>
            <div class="bg-light border text-muted d-flex align-items-center justify-content-center rounded entity-row-thumb-placeholder">
                <i class="fa-solid fa-camera-retro"></i>
            </div>
        <?php endif; ?>
    </td>
    <td>
        <div class="fw-bold text-dark"><?= $e($t['name']) ?></div>
        <?php if(!empty($t['product_type_name'])): ?>
            <small class="text-muted">
                <?= $e($t['product_type_name']) ?>
                <?php if ($t['item_count'] > 0): ?>
                    (<?= $t['item_count'] ?> Item<?php if ($t['item_count'] > 1): ?>s<?php endif; ?>)
                <?php endif; ?>
            </small>
        <?php endif; ?>
    </td>
    <td>
        <div><?= $e($t['universe_name'] ?? '—') ?></div>
        <small class="text-muted"><?= $e($t['entertainment_source_name'] ?? '—') ?></small>
    </td>
    
    <td>
        <div><?= $e($t['manufacturer_name'] ?? '—') ?></div>
        <small class="text-muted"><?= $e($t['toy_line_name'] ?? '—') ?></small>
    </td>
    <td>
        <div><?= $e($t['year_released'] ?? '—') ?></div>
        <small class="text-muted"><?= $e($t['wave'] ?? '—') ?></small>
    </td>
    <td class="text-center">
        <?php if ($t['collection_count'] > 0): ?>
            <span class="badge text-bg-light border  border-secondary">Owned (<?= $t['collection_count'] ?>)</span>
        <?php else: ?>
            <span class="badge text-bg-light border  border-secondary-subtle text-muted">Missing</span>
        <?php endif; ?>
    </td>
    <td class="text-end pe-3">
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary btn-edit" data-id="<?= $e($t['id']) ?>" title="Edit Catalog Data">
                <i class="fa-solid fa-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary btn-delete" data-id="<?= $e($t['id']) ?>" title="Delete">
                <i class="fa-solid fa-trash-alt"></i>
            </button>
        </div>
    </td>
</tr>
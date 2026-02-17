<tr data-id="<?= $e($g['id']) ?>">
    <td class="ps-3">
        <div class="fw-bold"><?= $e($g['name']) ?></div>
        <small class="text-muted"><?= $e($g['slug']) ?></small>
    </td>
    <td class="text-center">
        <?php if (!empty($g['abbreviation'])): ?>
            <span class="badge bg-secondary"><?= $e($g['abbreviation']) ?></span>
        <?php else: ?>
            <span class="text-muted">-</span>
        <?php endif; ?>
    </td>
    <td>
        <div class="text-truncate" style="max-width: 250px;">
            <?= $e($g['description'] ?? '-') ?>
        </div>
    </td>
    <td class="text-center">
        <span class="badge bg-light text-dark border">
            <?= $e($g['sort_order']) ?>
        </span>
    </td>
    <td class="text-end pe-3">
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary btn-edit" 
                    data-id="<?= $e($g['id']) ?>" 
                    data-json='<?= json_encode($g, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>'>
                <i class="fa-solid fa-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary btn-delete" data-id="<?= $e($g['id']) ?>">
                <i class="fa-solid fa-trash-alt"></i>
            </button>
        </div>
    </td>
</tr>
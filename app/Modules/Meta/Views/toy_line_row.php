<tr data-id="<?= $e($t['id']) ?>">
    <td class="ps-3">
        <div class="fw-bold"><?= $e($t['name']) ?></div>
        <small class="text-muted"><?= $e($t['slug']) ?></small>
    </td>
    <td>
        <?= $e($t['manufacturer_name'] ?? 'Unknown') ?>
    </td>
    <td>
        <?= $e($t['universe_name'] ?? 'Unknown') ?>
    </td>
    <td class="text-center">
        <?php if($t['show_on_dashboard']): ?>
            Visible
        <?php else: ?>
            Hidden
        <?php endif; ?>
    </td>
    <td class="text-end pe-3">
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary btn-edit" 
                    data-id="<?= $e($t['id']) ?>" 
                    data-json='<?= json_encode($t, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>'>
                <i class="fa-solid fa-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary btn-delete" data-id="<?= $e($t['id']) ?>">
                <i class="fa-solid fa-trash-alt"></i>
            </button>
        </div>
    </td>
</tr>
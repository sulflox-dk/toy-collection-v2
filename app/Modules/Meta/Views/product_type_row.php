<tr data-id="<?= $e($type['id']) ?>">
    <td class="ps-3">
        <div class="fw-bold"><?= $e($type['name']) ?></div>
        <small class="text-muted"><?= $e($type['slug']) ?></small>
    </td>
    <td>
        <div class="text-muted text-truncate" style="max-width: 400px;">
            <?= $e($type['description'] ?? '-') ?>
        </div>
    </td>
    <td class="text-end pe-3">
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary btn-edit" 
                    data-id="<?= $e($type['id']) ?>" 
                    data-json='<?= json_encode($type, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>'>
                <i class="fa-solid fa-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary btn-delete" data-id="<?= $e($type['id']) ?>">
                <i class="fa-solid fa-trash-alt"></i>
            </button>
        </div>
    </td>
</tr>
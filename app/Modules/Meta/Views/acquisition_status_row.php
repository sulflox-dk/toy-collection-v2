<tr data-id="<?= $e($status['id']) ?>">
    <td class="ps-3">
        <div class="fw-bold"><?= $e($status['name']) ?></div>
        <small class="text-muted"><?= $e($status['slug']) ?></small>
    </td>
    <td class="text-center">
        <span class="badge bg-light text-dark border">
            <?= $e($status['sort_order']) ?>
        </span>
    </td>
    <td class="text-end pe-3">
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary btn-edit" 
                    data-id="<?= $e($status['id']) ?>" 
                    data-json='<?= json_encode($status, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>'>
                <i class="fa-solid fa-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary btn-delete" data-id="<?= $e($status['id']) ?>">
                <i class="fa-solid fa-trash-alt"></i>
            </button>
        </div>
    </td>
</tr>
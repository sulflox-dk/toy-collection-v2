<tr data-id="<?= $e($tier['id']) ?>">
    <td class="ps-3">
        <div class="fw-bold"><?= $e($tier['name']) ?></div>
        <small class="text-muted"><?= $e($tier['slug']) ?></small>
    </td>
    <td class="text-center">
        <span class="badge bg-light text-dark border">
            <?= $e($tier['sort_order']) ?>
        </span>
    </td>
    <td class="text-end pe-3">
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary btn-edit" 
                    data-id="<?= $e($tier['id']) ?>" 
                    data-json='<?= json_encode($tier, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>'>
                <i class="fa-solid fa-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary btn-delete" data-id="<?= $e($tier['id']) ?>">
                <i class="fa-solid fa-trash-alt"></i>
            </button>
        </div>
    </td>
</tr>
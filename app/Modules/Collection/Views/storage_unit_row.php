<tr data-id="<?= $su['id'] ?>">
    <td class="ps-3">
        <span class="badge bg-secondary"><?= $e($su['box_code'] ?? 'N/A') ?></span>
    </td>
    <td>
        <div class="fw-bold"><?= $e($su['name']) ?></div>
    </td>
    <td>
        <?= $e($su['location'] ?? '') ?>
    </td>
    <td>
        <div class="d-inline-block text-truncate text-muted small" style="max-width: 250px;">
            <?= $e($su['description'] ?? '') ?>
        </div>
    </td>
    <td class="text-end pe-3">
        <div class="btn-group">    
            <button class="btn btn-sm btn-outline-secondary btn-edit" 
                    data-id="<?= $su['id'] ?>" 
                    data-json='<?= json_encode($su, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>'>
                <i class="fa-solid fa-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary btn-delete" data-id="<?= $su['id'] ?>">
                <i class="fa-solid fa-trash-alt"></i>
            </button>
        </div>
    </td>
</tr>
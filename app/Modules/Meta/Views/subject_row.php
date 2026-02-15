<tr data-id="<?= $e($s['id']) ?>">
    <td class="ps-3">
        <div class="fw-bold"><?= $e($s['name']) ?></div>
        <small class="text-muted"><?= $e($s['slug']) ?></small>
    </td>
    <td>
        <?= $e($s['type']) ?>
    </td>
    <td>
        <?= $e($s['universe_name'] ?? 'Unknown') ?>
    </td>
    <td>
        <div class="text-muted text-truncate" style="max-width: 200px;">
            <?= $e($s['description'] ?? '') ?>
        </div>
    </td>
    <td class="text-end pe-3">
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary btn-edit" 
                    data-id="<?= $e($s['id']) ?>" 
                    data-json='<?= json_encode($s, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>'>
                <i class="fa-solid fa-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary btn-delete" data-id="<?= $e($s['id']) ?>">
                <i class="fa-solid fa-trash-alt"></i>
            </button>
        </div>
    </td>
</tr>
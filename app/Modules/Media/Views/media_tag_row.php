<tr data-id="<?= $e($t['id']) ?>">
    <td class="ps-3">
        <div class="fw-bold"><i class="fa-solid fa-tag text-muted me-2 small"></i><?= $e($t['name']) ?></div>
        <small class="text-muted ms-4"><?= $e($t['slug']) ?></small>
    </td>
    <td class="text-center">
        <span class="badge bg-light text-dark border">
            <i class="fa-solid fa-image me-1 text-muted"></i> <?= $t['usage_count'] ?? 0 ?>
        </span>
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
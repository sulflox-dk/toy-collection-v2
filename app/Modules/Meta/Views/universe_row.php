<tr data-id="<?= $u['id'] ?>">
    <td class="ps-3 fw-bold">
        <?= $e($u['name']) ?>
    </td>
    <td>
        <?= $e($u['slug']) ?>
    </td>
    <td>
        <div class="d-inline-block text-truncate" style="width: 140px;"><?= $e($u['description'] ?? '') ?></div>
    </td>
    <td class="text-center">
        <?php if($u['show_on_dashboard']): ?>
            Visible
        <?php else: ?>
            Hidden
        <?php endif; ?>
    </td>
    <td class="text-center">
        <span class="badge bg-light text-dark border">
            <i class="fa-solid fa-list me-1"></i> <?= $u['lines_count'] ?? 0 ?>
        </span>
    </td>
    <td class="text-end pe-3">
        <div class="btn-group">    
            <button class="btn btn-sm btn-outline-secondary btn-edit" 
                    data-id="<?= $u['id'] ?>" 
                    data-json='<?= json_encode($u, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>'>
                <i class="fa-solid fa-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary btn-delete" data-id="<?= $u['id'] ?>">
                <i class="fa-solid fa-trash-alt"></i>
            </button>
        </div>
    </td>
</tr>
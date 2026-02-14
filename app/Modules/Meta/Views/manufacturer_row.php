<tr data-id="<?= $e($m['id']) ?>">
    <td class="ps-3 fw-bold">
        <?= $e($m['name']) ?>
    </td>
    <td>
        <?= $e($m['slug']) ?>
    </td>
    <td class="text-center">
        <?php if($m['show_on_dashboard']): ?>
            Visible
        <?php else: ?>
            Hidden
        <?php endif; ?>
    </td>
    <td class="text-center">
        <span class="badge bg-light text-dark border">
            <i class="fa-solid fa-list me-1"></i> <?= $m['lines_count'] ?? 0 ?>
        </span>
    </td>
    <td class="text-end pe-3">
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary btn-edit" 
                    data-id="<?= $e($m['id']) ?>"
                    data-json='<?= json_encode($m, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>'>
                <i class="fa-solid fa-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary btn-delete" data-id="<?= $e($m['id']) ?>">
                <i class="fa-solid fa-trash-alt"></i>
            </button>
        </div>
    </td>
</tr>
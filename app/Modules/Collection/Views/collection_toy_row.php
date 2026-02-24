<tr data-id="<?= $e($t['id']) ?>" class="align-middle">
    <td class="ps-3 text-center">
        <?php if(!empty($t['image_path'])): ?>
            <img src="<?= $e($t['image_path']) ?>" alt="Thumbnail" class="img-thumbnail entity-row-thumb">
        <?php else: ?>
            <div class="bg-light border text-muted d-flex align-items-center justify-content-center rounded entity-row-thumb-placeholder">
                <i class="fa-solid fa-camera-retro"></i>
            </div>
        <?php endif; ?>
    </td>
    <td>
        <div class="fw-bold text-dark"><?= $e($t['toy_name']) ?></div>
        <small class="text-muted">Added: <?= date('Y-m-d', strtotime($t['created_at'])) ?></small>
    </td>
    <td>
        <div><?= $e($t['universe_name'] ?? '—') ?></div>
        <small class="text-muted"><?= $e($t['toy_line_name'] ?? '—') ?></small>
    </td>
    <td>
        <div>
            <span class="badge bg-secondary"><?= $e($t['acquisition_status'] ?? 'Unknown') ?></span>
        </div>
        <small class="text-muted"><?= $e($t['condition_name'] ?? 'Not Graded') ?></small>
    </td>
    <td>
        <?php if($t['storage_unit_name']): ?>
            <div class="fw-medium"><?= $e($t['storage_box_code'] ?? '') ?></div>
            <small class="text-muted text-truncate d-inline-block" style="max-width: 150px;">
                <?= $e($t['storage_unit_name']) ?>
            </small>
        <?php else: ?>
            <span class="text-muted">—</span>
        <?php endif; ?>
    </td>
    <td class="text-end pe-3">
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-secondary" title="Edit Collection Item">
                <i class="fa-solid fa-pencil"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary btn-delete" data-id="<?= $e($t['id']) ?>" title="Delete">
                <i class="fa-solid fa-trash-alt"></i>
            </button>
        </div>
    </td>
</tr>
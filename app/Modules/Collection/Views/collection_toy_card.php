<div class="col">
    <div class="card h-100 shadow-sm border-0 position-relative">
        
        <div class="bg-light d-flex align-items-center justify-content-center border-bottom entity-card-img-wrap">
            <?php if(!empty($t['image_path'])): ?>
                <img src="<?= $e($t['image_path']) ?>" alt="Thumbnail">
            <?php else: ?>
                <div class="text-center text-muted opacity-50">
                    <i class="fa-solid fa-camera-retro fa-3x mb-2"></i><br>
                    <small>No Image</small>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="card-body d-flex flex-column">
            <h6 class="card-title fw-bold text-dark mb-1 text-truncate" title="<?= $e($t['toy_name']) ?>">
                <?= $e($t['toy_name']) ?>
            </h6>

            <div class="text-muted small mb-2 text-truncate">
                <?= $e($t['universe_name'] ?? '—') ?> &bull; <?= $e($t['toy_line_name'] ?? '—') ?>
            </div>

            <div class="small mb-2">
                <strong>Status:</strong> <?= $e($t['acquisition_status'] ?? 'Unknown') ?>
                <?php if($t['packaging_name']): ?>
                    (<?= $e($t['packaging_name']) ?>)
                <?php endif; ?>
            </div>

            <div class="text-muted small mb-2">
                <strong>Condition:</strong> <?= $e($t['condition_name'] ?? 'Not Graded') ?>
            </div>

            <?php if($t['storage_unit_name']): ?>
            <div class="mt-auto pt-2">
                <span class="badge bg-light text-dark border w-100 text-start text-truncate">
                    <i class="fa-solid fa-box archive me-1"></i> 
                    <?= $e($t['storage_box_code'] ? $t['storage_box_code'].' - '.$t['storage_unit_name'] : $t['storage_unit_name']) ?>
                </span>
            </div>
            <?php endif; ?>

            <div class="mt-3 pt-3 border-top d-flex justify-content-end align-items-center">
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-secondary" title="Edit Collection Item">
                        <i class="fa-solid fa-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-delete" data-id="<?= $e($t['id']) ?>" title="Delete">
                        <i class="fa-solid fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
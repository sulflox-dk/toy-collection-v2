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
            <h6 class="card-title fw-bold text-dark mb-1 text-truncate" title="<?= $e($t['name']) ?>">
                <?= $e($t['name']) ?>
            </h6>

            <div class="text-muted small mb-2 text-truncate">
                <?= $e($t['product_type_name'] ?? '—') ?>
                <?php if ($t['item_count'] > 0): ?>
                    (<?= $t['item_count'] ?> Item<?php if ($t['item_count'] > 1): ?>s<?php endif; ?>)
                <?php endif; ?> 
            </div>

            <div class="small mb-2">
                <?php 
                    $meta = [];
                    if($t['universe_name']) $meta[] = $e($t['universe_name']);
                    if($t['entertainment_source_name']) $meta[] = $e($t['entertainment_source_name']);
                    echo implode(' &bull; ', array_map('htmlspecialchars', $meta));
                ?>
            </div>

            <div class="text-muted small mb-1">
                <?php 
                    $meta = [];
                    if($t['manufacturer_name']) $meta[] = $e($t['manufacturer_name']);
                    if($t['toy_line_name']) $meta[] = $e($t['toy_line_name']);
                    echo implode(' &bull; ', array_map('htmlspecialchars', $meta));
                ?>
            </div>
            <div class="text-muted small mb-3">
                <?php 
                    $meta = [];
                    if($t['year_released']) $meta[] = $e($t['year_released']);
                    if($t['wave']) $meta[] = $e($t['wave']);
                    echo implode(' &bull; ', array_map('htmlspecialchars', $meta));
                ?>
            </div>

            <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                <div class="d-flex">
                <div clasS="me-3">
                <?php if ($t['collection_count'] > 0): ?>
                    <span class="badge text-bg-light border  border-secondary">Owned (<?= $t['collection_count'] ?>)</span>
                <?php else: ?>
                    <span class="badge text-bg-light border  border-secondary-subtle text-muted">Missing</span>
                <?php endif; ?>
                </div>
                </div>

                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-secondary" 
                            onclick="CatalogWizard.editToy(<?= $e($t['id']) ?>, <?= $e($t['universe_id']) ?>)" 
                            title="Edit Catalog Data">
                        <i class="fa-solid fa-pencil"></i>
                    </button>
                    
                    <button type="button" class="btn btn-sm btn-outline-secondary" 
                            onclick="CatalogWizard.editPhotos(<?= $e($t['id']) ?>)" 
                            title="Manage Photos">
                        <i class="fa-solid fa-camera"></i>
                    </button>

                    <button type="button" class="btn btn-sm btn-outline-secondary btn-delete" 
                            data-id="<?= $e($t['id']) ?>" 
                            title="Delete">
                        <i class="fa-solid fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
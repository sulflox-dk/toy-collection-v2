<?php
// Calculate missing parts status
$missingCount = $t['items_total_count'] - $t['items_owned_count'];
$hasMissing = $missingCount > 0;
?>
<div class="col">
    <div class="card h-100 toy-card shadow-sm border-0 position-relative">
        
        <div class="position-relative bg-light border-bottom d-flex align-items-center justify-content-center p-0 entity-card-img-wrap" style="height: 260px;">
            <?php if(!empty($t['image_path'])): ?>
                <img src="<?= $e($t['image_path']) ?>" 
                     style="max-height: 100%; max-width: 100%; object-fit: contain; <?= $t['is_stock_image'] ? 'opacity: 0.6;' : '' ?>" 
                     loading="lazy" alt="Toy Photo">

                <?php if($t['is_stock_image']): ?>
                    <span class="position-absolute top-50 start-50 translate-middle badge bg-secondary rounded-pill text-uppercase" title="This is a catalog image" style="opacity:0.8; font-weight:500; font-size:1.2rem;">
                        Catalog Photo
                    </span>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center text-muted opacity-25">
                    <i class="fa-solid fa-camera fa-4x mb-2"></i><br>No Image
                </div>
            <?php endif; ?>

            <?php if($t['acquisition_status'] && $t['acquisition_status'] !== 'Arrived'): ?>
                <span class="position-absolute top-0 end-0 m-2 badge rounded-pill bg-secondary shadow-sm text-uppercase" style="font-size:0.6rem;">
                    <?= $e($t['acquisition_status']) ?>
                </span>
            <?php endif; ?>
        </div>
        
        <div class="card-body d-flex flex-column">
            
            <h6 class="card-title fw-bold text-dark mb-1 text-truncate" title="<?= $e($t['toy_name']) ?>">
                <?= $e($t['toy_name']) ?>
            </h6>
            
            <div class="small text-secondary mb-1">
                <?php 
                    $meta1 = [];
                    if($t['year_released']) $meta1[] = $t['year_released'];
                    if($t['product_type_name']) $meta1[] = $t['product_type_name'];
                    echo implode(' &bull; ', array_map('htmlspecialchars', $meta1));
                ?>
            </div>

            <?php if(!empty($t['source_name'])): ?>
                <div class="small text-secondary mb-1">
                    <?php
                        $meta2 = [$t['source_name']];
                        if($t['source_year']) $meta2[] = $t['source_year'];
                        if($t['source_type']) $meta2[] = $t['source_type'];
                        echo implode(' &bull; ', array_map('htmlspecialchars', $meta2));
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="text-secondary small mb-3">
                <?php
                    $meta3 = [];
                    if($t['manufacturer_name']) $meta3[] = $t['manufacturer_name'];
                    if($t['toy_line_name']) $meta3[] = $t['toy_line_name'];
                    if(empty($meta3)) $meta3[] = '-';
                    echo implode(' &bull; ', array_map('htmlspecialchars', $meta3));
                ?>
            </div>

            <div class="border-top pt-2 pb-2 small">
                <div>Condition: <?= $e($t['condition_name'] ?? '-') ?></div>
                <div>
                    <span>Storage: <?= $e($t['storage_box_code'] ?? '?') ?></span>
                    <span> &bull; </span>
                    <span>ID: <?= $e($t['id']) ?></span>
                </div>
            </div>

            <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                
                <div class="d-flex align-items-center overflow-hidden me-2">
                    <?php if($t['items_total_count'] > 0): ?>
                        <?php if($hasMissing): ?>
                            <span class="badge text-bg-light border border-warning-subtle text-warning text-truncate" 
                                  title="Missing: <?= $e($t['missing_parts_list'] ?: 'Unspecified parts') ?>">
                                Missing <?= $missingCount ?> Item<?= $missingCount > 1 ? 's' : '' ?>
                            </span>
                        <?php else: ?>
                            <span class="badge text-bg-light border border-success-subtle text-success">
                                Complete
                            </span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="badge text-bg-light border border-secondary-subtle text-muted">
                            No Parts
                        </span>
                    <?php endif; ?>
                </div>

                <div class="btn-group flex-shrink-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-edit" title="Edit Collection Details">
                        <i class="fa-solid fa-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-media" title="Manage Photos">
                        <i class="fa-solid fa-camera"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-delete" data-id="<?= $e($t['id']) ?>" title="Remove from Collection">
                        <i class="fa-solid fa-trash-alt"></i>
                    </button>
                </div>
                
            </div>
            
        </div>
    </div>
</div>
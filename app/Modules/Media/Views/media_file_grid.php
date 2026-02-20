<div id="media-grid-container" class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-3">
    <?php if (empty($files)): ?>
        <div class="col-12 text-center py-5 text-muted">
            <i class="fa-regular fa-images fa-2x mb-3"></i>
            <p>No media files found.</p>
        </div>
    <?php else: ?>
        <?php foreach ($files as $f): ?>
            <div class="col">
                <div class="card h-100 shadow-sm file-card" data-id="<?= $e($f['id']) ?>">
                     
                    <div class="ratio ratio-1x1 bg-light border-bottom lightbox-trigger" style="cursor: pointer;" title="Click to view full size">
                        <?php if (in_array(strtolower($f['file_type']), ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])): ?>
                            <img src="<?= $e($baseUrl . $f['filepath']) ?>" 
                                 class="card-img-top object-fit-cover" 
                                 alt="<?= $e($f['alt_text']) ?>"
                                 loading="lazy">
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center text-muted">
                                <i class="fa-solid fa-file-pdf fa-3x"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="card-body p-2 text-center">
                        <div class="small fw-bold text-truncate" title="<?= $e($f['title'] ?: $f['original_name']) ?>">
                            <?= $e($f['title'] ?: $f['original_name']) ?>
                        </div>
                        <div class="text-muted" style="font-size: 0.75rem;">
                            <?= strtoupper(pathinfo($f['filename'], PATHINFO_EXTENSION)) ?>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-top-0 p-2">
                        <div class="btn-group w-100 btn-group-sm">
                            <button class="btn btn-outline-secondary btn-edit" 
                                    data-id="<?= $e($f['id']) ?>"
                                    data-json='<?= json_encode($f, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>'>
                                <i class="fa-solid fa-pencil"></i>
                            </button>
                            
                            <button class="btn btn-outline-secondary btn-delete" 
                                    data-id="<?= $e($f['id']) ?>">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="mt-4">
    <?= $this->renderPartial('common/pagination', ['pagination' => $pagination]) ?>
</div>
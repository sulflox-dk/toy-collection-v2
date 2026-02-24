<?php
$tagsJson = json_encode($availableTags ?? [], JSON_HEX_APOS | JSON_HEX_QUOT);
?>

<div class="modal-header bg-dark text-white border-0">
    <h5 class="modal-title">
        <i class="fa-solid fa-camera me-2"></i>Collection Photos
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body p-4 bg-light d-flex flex-column gap-4 overflow-auto" id="media-manager-container" data-tags='<?= $tagsJson ?>'>

    <div id="step3-success-alert" class="alert alert-success border-0 shadow-sm mb-0" style="transition: opacity 0.3s ease;">
        <i class="fa-solid fa-check-circle me-2"></i> <strong>Success!</strong> Collection toy saved.
    </div>

    <div>
        <h6 class="text-uppercase fw-bold text-muted small mb-2">Main Toy (Your Photos)</h6>
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-1 fw-bold"><?= $e($collectionToy['toy_name']) ?></h5>
                        <span class="text-muted small">Collection Entry #<?= $collectionToy['id'] ?></span>
                    </div>
                    <div>
                        <button class="btn btn-outline-dark btn-sm" onclick="MediaPicker.open('collection_toys', <?= $collectionToy['id'] ?>)">
                            <i class="fa-solid fa-plus me-1"></i> Add Photo
                        </button>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2" id="preview-collection_toys-<?= $collectionToy['id'] ?>">
                    <div class="text-muted small w-100 text-center py-3 bg-light border rounded border-dashed">No images attached yet.</div>
                </div>
            </div>
        </div>
    </div>

    <?php if(!empty($items)): ?>
        <div>
            <h6 class="text-uppercase fw-bold text-muted small mb-2">Included Items</h6>

            <div class="vstack gap-3">
                <?php foreach($items as $item): ?>
                    <div class="card shadow-sm border-0">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <h6 class="mb-0 fw-bold"><?= $e($item['subject_name']) ?></h6>
                                        <span class="badge bg-secondary" style="font-size: 0.65rem;"><?= $e($item['subject_type']) ?></span>
                                    </div>
                                    <?php if(!empty($item['description'])): ?>
                                        <div class="small text-muted mt-1">
                                            <i class="fa-solid fa-info-circle me-1"></i><?= $e($item['description']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="MediaPicker.open('collection_toy_items', <?= $item['id'] ?>)">
                                        <i class="fa-solid fa-plus me-1"></i> Add Photo
                                    </button>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2" id="preview-collection_toy_items-<?= $item['id'] ?>">
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<div class="modal-footer bg-light justify-content-between border-top flex-shrink-0">
    <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal" onclick="window.collectionToyManager.loadList()">
        Finish without adding photos
    </button>
    <button type="button" class="btn btn-dark px-4" data-bs-dismiss="modal" onclick="window.collectionToyManager.loadList()">
        Done <i class="fa-solid fa-check ms-1"></i>
    </button>
</div>

<?= $this->renderPartial('common/media_manager') ?>

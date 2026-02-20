<?php
// Prepare the tags for javascript
$tagsJson = json_encode($availableTags ?? [], JSON_HEX_APOS | JSON_HEX_QUOT);
?>

<div class="modal-header bg-dark text-white border-0">
    <h5 class="modal-title">
        <i class="fa-solid fa-camera me-2"></i>Add Photos
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body p-4 bg-light d-flex flex-column gap-4 overflow-auto" id="media-manager-container" data-tags='<?= $tagsJson ?>'>
    
    <div id="step3-success-alert" class="alert alert-success border-0 shadow-sm mb-0" style="transition: opacity 0.3s ease;">
        <i class="fa-solid fa-check-circle me-2"></i> <strong>Success!</strong> Catalog Toy saved.
    </div>

    <div>
        <h6 class="text-uppercase fw-bold text-muted small mb-2">Main Toy (Packaging / Box / Complete)</h6>
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-1 fw-bold"><?= $e($toy['name']) ?></h5>
                        <span class="text-muted small">Primary Entry</span>
                    </div>
                    <div>
                        <button class="btn btn-outline-dark btn-sm" onclick="CatalogWizard.openMediaPicker('catalog_toys', <?= $toy['id'] ?>)">
                            <i class="fa-solid fa-plus me-1"></i> Add Photo
                        </button>
                    </div>
                </div>
                
                <div class="d-flex flex-wrap gap-2" id="preview-catalog_toys-<?= $toy['id'] ?>">
                    <div class="text-muted small w-100 text-center py-3 bg-light border rounded border-dashed">No images attached yet.</div>
                </div>
            </div>
        </div>
    </div>

    <?php if(!empty($items)): ?>
        <div>
            <h6 class="text-uppercase fw-bold text-muted small mb-2">Included Items (Figures / Accessories)</h6>
            
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
                                    <button class="btn btn-outline-secondary btn-sm" onclick="CatalogWizard.openMediaPicker('catalog_toy_items', <?= $item['id'] ?>)">
                                        <i class="fa-solid fa-plus me-1"></i> Add Photo
                                    </button>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2" id="preview-catalog_toy_items-<?= $item['id'] ?>">
                                </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<div class="modal-footer bg-light justify-content-between border-top flex-shrink-0">
    <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal" onclick="window.catalogToyManager.loadList()">
        Finish without adding photos
    </button>
    <button type="button" class="btn btn-dark px-4" data-bs-dismiss="modal" onclick="window.catalogToyManager.loadList()">
        Done <i class="fa-solid fa-check ms-1"></i>
    </button>
</div>

<div id="mediaPickerOverlay" class="position-absolute top-0 start-0 w-100 h-100 bg-white d-none flex-column" style="z-index: 1060;">
    <div class="modal-header bg-dark text-white border-0 flex-shrink-0">
        <h5 class="modal-title"><i class="fa-solid fa-image me-2"></i>Select Media</h5>
        <button type="button" class="btn-close btn-close-white" onclick="CatalogWizard.closeMediaPicker()"></button>
    </div>
    
    <div class="p-3 border-bottom bg-light flex-shrink-0">
        <ul class="nav nav-pills" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-upload" type="button">Upload New</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-library" type="button">Search Library</button>
            </li>
        </ul>
    </div>

    <div class="tab-content flex-grow-1 overflow-auto p-4">
        
        <div class="tab-pane fade show active h-100" id="tab-upload">
            <div id="mediaDropZone" class="d-flex flex-column align-items-center justify-content-center h-100 border rounded" style="border-style: dashed !important; border-width: 2px !important; transition: all 0.2s ease-in-out;">
                <i class="fa-solid fa-cloud-arrow-up fa-3x text-muted mb-3"></i>
                <h5>Drag & Drop images here</h5>
                <p class="text-muted mb-3">or</p>
                <label class="btn btn-primary">
                    Browse Files
                    <input type="file" class="d-none" id="mediaUploadInput" accept="image/*" multiple onchange="CatalogWizard.handleFileUpload(this)">
                </label>
            </div>
        </div>

        <div class="tab-pane fade h-100 d-flex flex-column" id="tab-library">
            <div class="input-group mb-3">
                <span class="input-group-text bg-white"><i class="fa-solid fa-search text-muted"></i></span>
                <input type="text" class="form-control" id="mediaSearchInput" placeholder="Search existing images by filename or tags..." onkeyup="CatalogWizard.searchLibrary(this.value)">
            </div>
            
            <div class="flex-grow-1 border rounded bg-white p-3 overflow-auto" id="mediaSearchResults">
                <div class="text-center text-muted mt-5">Type to search your image library...</div>
            </div>
        </div>

    </div>
</div>

<template id="mediaEditRowTemplate">
    <div class="card shadow-sm border-0 bg-white media-edit-row" data-media-id="">
        <div class="card-body p-3 d-flex flex-column flex-md-row gap-4">
            
            <div class="d-flex flex-column gap-2" style="width: 150px; flex-shrink: 0;">
                <div class="border rounded bg-light d-flex align-items-center justify-content-center p-1" style="height: 150px;">
                    <img class="preview-img" src="" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger w-100 btn-unlink">
                    <i class="fa-solid fa-unlink me-1"></i> Remove
                </button>
            </div>
            
            <div class="flex-grow-1 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 text-muted small text-uppercase fw-bold">Image Details</h6>
                    <span class="badge bg-success opacity-0 save-indicator" style="transition: opacity 0.3s;"><i class="fa-solid fa-check me-1"></i>Saved</span>
                </div>
                
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Title</label>
                        <input type="text" class="form-control form-control-sm meta-input meta-title" placeholder="Image Title">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Alt Text</label>
                        <input type="text" class="form-control form-control-sm meta-input meta-alt" placeholder="For screen readers">
                    </div>
                    <div class="col-12">
                        <label class="form-label small text-muted mb-1">Description</label>
                        <textarea class="form-control form-control-sm meta-input meta-desc" rows="2" placeholder="Describe the photo..."></textarea>
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label small text-muted mb-2">Tags</label>
                        <div class="d-flex flex-wrap gap-2 tag-container">
                            </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>
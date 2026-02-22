<!-- =======================================================================
   Media Manager — Shared partial for the MediaPicker overlay and edit row.

   Prerequisites:
     - The page must include an element with id="media-manager-container"
       and a data-tags='[...]' attribute (used by MediaPicker.buildTagPills).
     - core/media-picker.js must be loaded (included in layout.php).
   ======================================================================= -->

<div id="mediaPickerOverlay" class="position-absolute top-0 start-0 w-100 h-100 bg-white d-none flex-column" style="z-index: 1060;">
    <div class="modal-header bg-dark text-white border-0 flex-shrink-0">
        <h5 class="modal-title"><i class="fa-solid fa-image me-2"></i>Select Media</h5>
        <button type="button" class="btn-close btn-close-white" onclick="MediaPicker.close()"></button>
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
                    <input type="file" class="d-none" id="mediaUploadInput" accept="image/*" multiple onchange="MediaPicker.handleFileUpload(this)">
                </label>
            </div>
        </div>

        <div class="tab-pane fade h-100 d-flex flex-column" id="tab-library">
            <div class="input-group mb-3">
                <span class="input-group-text bg-white"><i class="fa-solid fa-search text-muted"></i></span>
                <input type="text" class="form-control" id="mediaSearchInput" placeholder="Search existing images by filename or tags..." onkeyup="MediaPicker.searchLibrary(this.value)">
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

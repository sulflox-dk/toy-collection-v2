<?php
// Custom Header for Media Library
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Media Library</h1>
        <p class="text-muted mb-0">Manage images and documents.</p>
    </div>
    <div>
        <button type="button" class="btn btn-outline-primary me-2" id="btn-toggle-dropzone">
            <i class="fa-solid fa-images me-1"></i> Upload Multiple Files
        </button>
        <button type="button" class="btn btn-primary" id="btn-add-media">
            <i class="fa-solid fa-plus me-1"></i> Upload File
        </button>
    </div>
</div>

<div id="upload-zone" class="card mb-4 d-none border-dashed shadow-sm">
    <div class="card-body text-center p-5">
        <div class="mb-3">
            <i class="fa-solid fa-cloud-arrow-up fa-3x text-primary opacity-50"></i>
        </div>
        <h5 class="mb-1">Drag and drop files here</h5>
        <p class="text-muted small mb-3">Upload multiple images quickly without entering metadata right away.</p>
        
        <input type="file" id="file-input" class="d-none" multiple accept="image/*,application/pdf">
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('file-input').click()">
            Browse Files
        </button>
        
        <div id="upload-progress" class="progress mt-4 d-none" style="height: 6px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
        </div>
    </div>
</div>

<div class="row mb-4 g-2">
    <div class="col-md-4">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
            <input type="text" class="form-control border-start-0 ps-0" id="search-input" placeholder="Search media...">
        </div>
    </div>
    
    <div class="col-md-4">
        <select class="form-select data-filter" name="attachment_type">
            <option value="">All Media Files</option>
            <option value="catalog_toys">Attached to Catalog Toys</option>
            <option value="collection_toys">Attached to Collection Toys</option>
            <option value="universes">Attached to Universes</option>
            <option value="manufacturers">Attached to Manufacturers</option>
            <option value="toy_lines">Attached to Toy Lines</option>
            <option value="sources">Attached to Entertainment Sources</option>
            <option value="unattached">No Attachments (Orphans)</option>
        </select>
    </div>
</div>

<div id="media-grid"></div>

<div class="modal fade" id="media-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Media Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="media-form" enctype="multipart/form-data">
                <div class="modal-body">
                    <?= $csrfField() ?>
                    <input type="hidden" name="id" id="media-id">
                    
                    <div class="text-center mb-3 d-none" id="preview-container">
                        <img id="preview-image" src="" class="img-fluid rounded border shadow-sm" style="max-height: 200px;">
                    </div>

                    <div class="mb-3" id="file-input-group">
                        <label class="form-label">Select File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="file" id="media-file-input" accept="image/*,application/pdf">
                        <div class="form-text">Allowed: jpg, png, gif, webp, pdf</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" placeholder="Image Title">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alt Text</label>
                        <input type="text" class="form-control" name="alt_text" placeholder="Accessibility text">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="Internal notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-link text-muted me-auto" data-bs-dismiss="modal">Cancel</button>
                    
                    <button type="submit" class="btn btn-primary" data-action="save-close">
                        <i class="fa-solid fa-cloud-arrow-up me-1 d-none" id="icon-upload"></i>
                        <span id="btn-save-text">Save</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade bg-dark bg-opacity-75" id="lightbox-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content bg-transparent border-0">
            
            <div class="modal-header border-0 pb-0">
                <h5 class="text-white mb-0 text-truncate w-75" id="lightbox-title"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body d-flex align-items-center justify-content-center position-relative p-0">
                
                <button class="btn btn-link text-white position-absolute start-0 top-50 translate-middle-y fs-1 text-decoration-none" id="lightbox-prev" style="z-index: 1050; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                
                <div class="text-center w-100 px-5">
                    <img id="lightbox-img" src="" class="img-fluid rounded shadow-lg d-none" style="max-height: 80vh; object-fit: contain;">
                    
                    <div id="lightbox-pdf-container" class="d-none text-white">
                        <i class="fa-solid fa-file-pdf mb-4" style="font-size: 8rem;"></i>
                        <br>
                        <a id="lightbox-pdf-link" href="#" target="_blank" class="btn btn-light rounded-pill px-4 shadow">
                            <i class="fa-solid fa-arrow-up-right-from-square me-2"></i> Open Document
                        </a>
                    </div>
                </div>

                <button class="btn btn-link text-white position-absolute end-0 top-50 translate-middle-y fs-1 text-decoration-none" id="lightbox-next" style="z-index: 1050; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
            
            <div class="modal-footer border-0 justify-content-center pt-0 pb-4">
                <button class="btn btn-outline-light me-3 px-4 rounded-pill" id="lightbox-btn-edit">
                    <i class="fa-solid fa-pencil me-1"></i> Edit
                </button>
                <button class="btn btn-outline-danger px-4 rounded-pill" id="lightbox-btn-delete">
                    <i class="fa-solid fa-trash-can me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

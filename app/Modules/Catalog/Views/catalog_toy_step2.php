<?php
$isEdit = !empty($toy['id']);

// Encode the full lists for JavaScript cascading logic
$jsonManufacturers = json_encode($manufacturers ?? [], JSON_HEX_APOS | JSON_HEX_QUOT);
$jsonToyLines = json_encode($toyLines ?? [], JSON_HEX_APOS | JSON_HEX_QUOT);
$jsonSources = json_encode($entertainmentSources ?? [], JSON_HEX_APOS | JSON_HEX_QUOT);
$jsonSubjects = json_encode($subjects ?? [], JSON_HEX_APOS | JSON_HEX_QUOT);
?>

<div class="modal-header bg-dark text-white border-0">
    <h5 class="modal-title">
        <i class="fa-solid fa-robot me-2"></i><?= $isEdit ? 'Edit Catalog Toy' : 'Add Catalog Toy' ?>
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<form id="catalogToyForm" class="d-flex flex-column" style="min-height: 0; flex: 1 1 auto;"
      data-manufacturers='<?= $jsonManufacturers ?>'
      data-toy-lines='<?= $jsonToyLines ?>'
      data-sources='<?= $jsonSources ?>'
      data-subjects='<?= $jsonSubjects ?>'
      onsubmit="event.preventDefault(); CatalogWizard.submitStep2();">

    <?= $csrfField() ?>
    
    <?php if($isEdit): ?><input type="hidden" name="id" value="<?= $toy['id'] ?>"><?php endif; ?>
    
    <div class="modal-body p-4 bg-light d-flex flex-column gap-4 overflow-auto">
        
        <div>
            <h6 class="text-uppercase fw-bold text-muted small mb-2">General Information</h6>
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1">Universe *</label>
                            <select class="form-select border-secondary" name="universe_id" id="catalog_universe_id" required>
                                <option value="">Select Universe...</option>
                                <?php foreach($universes as $u): ?>
                                    <option value="<?= $u['id'] ?>" <?= ($universeId == $u['id']) ? 'selected' : '' ?>><?= htmlspecialchars($u['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1">Manufacturer *</label>
                            <select class="form-select" name="manufacturer_id" id="catalog_manufacturer_id" data-selected="<?= $toy['manufacturer_id'] ?? '' ?>" required disabled>
                                <option value="">Select Universe first...</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1">Toy Line *</label>
                            <select class="form-select" name="toy_line_id" id="catalog_toy_line_id" data-selected="<?= $toy['toy_line_id'] ?? '' ?>" required disabled>
                                <option value="">Select Manufacturer first...</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label small text-muted mb-1">Toy Name *</label>
                            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($toy['name'] ?? '') ?>" required placeholder="e.g. Luke Skywalker (Tatooine)">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1">Release Year *</label>
                            <input type="number" class="form-control" name="year_released" value="<?= $toy['year_released'] ?? '' ?>" placeholder="e.g. 1978" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted mb-1">Product Type *</label>
                            <select class="form-select" name="product_type_id" required>
                                <option value="">Select Type...</option>
                                <?php foreach($productTypes as $pt): ?>
                                    <option value="<?= $pt['id'] ?>" <?= (($toy['product_type_id'] ?? 0) == $pt['id']) ? 'selected' : '' ?>><?= htmlspecialchars($pt['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted mb-1">Entertainment Source</label>
                            <select class="form-select" name="entertainment_source_id" id="catalog_entertainment_source_id" data-selected="<?= $toy['entertainment_source_id'] ?? '' ?>" disabled>
                                <option value="">Select Universe first...</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1">Wave</label>
                            <input type="text" class="form-control" name="wave" value="<?= htmlspecialchars($toy['wave'] ?? '') ?>" placeholder="e.g. Wave 1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1">Assortment / SKU</label>
                            <input type="text" class="form-control" name="assortment_sku" value="<?= htmlspecialchars($toy['assortment_sku'] ?? '') ?>" placeholder="e.g. 38240">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1">UPC</label>
                            <input type="text" class="form-control" name="upc" value="<?= htmlspecialchars($toy['upc'] ?? '') ?>" placeholder="Barcode">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="text-uppercase fw-bold text-muted small mb-0">Included Items</h6>
                <span class="badge bg-secondary rounded-pill" id="itemCountBadge">0 items</span>
            </div>

            <div id="itemsContainer" class="vstack gap-2 mb-3">
                </div>

            <button type="button" class="btn btn-outline-secondary w-100 py-2" onclick="CatalogWizard.addEmptyItemRow()" style="border-style: dashed;">
                <i class="fa-solid fa-plus me-2"></i> Add Item
            </button>
        </div>

    </div>

    <div class="modal-footer bg-light justify-content-between border-top flex-shrink-0">
        <button type="button" class="btn btn-link text-muted text-decoration-none" onclick="CatalogWizard.loadStep1()">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Universes
        </button>
        <button type="submit" class="btn btn-dark px-4">
            <?= $isEdit ? 'Save Changes' : 'Save & Add Photos <i class="fa-solid fa-arrow-right ms-1"></i>' ?>
        </button>
    </div>
</form>

<template id="itemRowTemplate">
    <div class="card item-row border shadow-sm">
        <div class="card-body p-3 position-relative">
            <button type="button" class="btn-close position-absolute top-0 end-0 m-2 small" onclick="CatalogWizard.removeItemRow(this)"></button>
            <div class="row g-2 align-items-end pe-4">
                
                <div class="col-md-5">
                    <label class="form-label small text-muted mb-1">Subject *</label>
                    <input type="hidden" name="items[{INDEX}][id]" class="item-id-input">
                    <input type="hidden" name="items[{INDEX}][subject_id]" class="item-subject-id" required>
                    
                    <div class="subject-selector-wrapper position-relative">
                        <div class="d-flex align-items-center border rounded px-2 py-1 bg-white shadow-sm" 
                             onclick="CatalogWizard.toggleSubjectSearch(this)"
                             style="cursor: pointer; min-height: 38px;">
                            <div class="me-2 text-muted d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-cube fs-5"></i>
                            </div>
                            <div class="flex-grow-1 lh-1">
                                <div class="subject-name fw-medium small text-truncate">Select Subject...</div>
                                <div class="subject-meta text-muted" style="font-size: 0.7rem; display: none;"></div>
                            </div>
                            <i class="fa-solid fa-chevron-down text-muted small ms-2"></i>
                        </div>
                        
                        <div class="subject-search-dropdown position-absolute w-100 bg-white border rounded shadow-lg mt-1 d-none" style="z-index: 1050; top: 100%;">
                            <div class="p-2 border-bottom bg-light">
                                <input type="text" class="form-control form-control-sm search-input" 
                                       placeholder="Type to search..." 
                                       onkeyup="CatalogWizard.filterSubjects(this)"
                                       autocomplete="off">
                            </div>
                            <div class="results-list overflow-auto" style="max-height: 250px;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-7">
                    <label class="form-label small text-muted mb-1">Description (Optional)</label>
                    <input type="text" class="form-control item-desc-input" name="items[{INDEX}][description]" placeholder="e.g. Standard release yellow lightsaber">
                </div>

            </div>
        </div>
    </div>
</template>
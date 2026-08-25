<?php
$isEdit = !empty($collectionToy['id']);
$ct = $collectionToy ?? [];

// Build a lookup map: catalog_toy_item_id => collection_toy_item row
$itemMap = [];
foreach ($collectionItems as $ci) {
    $itemMap[$ci['catalog_toy_item_id']] = $ci;
}
?>

<div class="modal-header bg-dark text-white border-0">
    <h5 class="modal-title">
        <i class="fa-solid <?= $isEdit ? 'fa-pencil' : 'fa-box-open' ?> me-2"></i>
        <?= $isEdit ? 'Edit Collection Entry' : 'Add to Collection' ?>
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<form id="collectionToyForm" class="d-flex flex-column" style="min-height: 0; flex: 1 1 auto;"
      onsubmit="event.preventDefault(); CollectionWizard.submitStep2();">

    <?= $csrfField() ?>

    <?php if($isEdit): ?>
        <input type="hidden" name="id" value="<?= $ct['id'] ?>">
    <?php endif; ?>
    <input type="hidden" name="catalog_toy_id" value="<?= $catalogToy['id'] ?>">

    <div class="modal-body p-4 bg-light d-flex flex-column gap-4 overflow-auto">

        <!-- Catalog Toy Header (read-only) -->
        <?php
            // Your own photo of this actual copy takes priority over the
            // catalog's generic default photo, when you have one.
            $headerImage = ($isEdit && !empty($ct['image_path'])) ? $ct['image_path'] : ($catalogToy['image_path'] ?? '');
            $headerImageTitle = ($isEdit && !empty($ct['image_path'])) ? 'Your photo (view/change in Add Photos)' : 'Catalog default photo';
        ?>
        <div class="card shadow-sm border-0 bg-white">
            <div class="card-body p-3">
                <div class="row g-3 align-items-center">
                    <div class="col-md-3">
                        <div class="bg-light border rounded d-flex align-items-center justify-content-center" style="width: 100%; aspect-ratio: 1 / 1;">
                            <?php if(!empty($headerImage)): ?>
                                <img src="<?= $e($headerImage) ?>" title="<?= $e($headerImageTitle) ?>" style="width:100%; height:100%; object-fit:contain;">
                            <?php else: ?>
                                <i class="fa-solid fa-cube fa-3x text-muted opacity-25"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-9 d-flex align-items-center gap-3">
                        <div class="flex-grow-1">
                            <h5 class="mb-1 fw-bold"><?= $e($catalogToy['name']) ?></h5>
                            <div class="small text-muted">
                                <?= $e(implode(' · ', array_filter([
                                    $catalogToy['universe_name'] ?? '',
                                    $catalogToy['toy_line_name'] ?? '',
                                    $catalogToy['manufacturer_name'] ?? '',
                                    $catalogToy['year_released'] ?? ''
                                ]))) ?>
                            </div>
                        </div>
                        <?php if(!$isEdit): ?>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="CollectionWizard.loadStep1()" title="Pick different toy">
                                <i class="fa-solid fa-arrow-left"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Rating -->
        <div>
            <h6 class="text-uppercase fw-bold text-muted small mb-2">Cherish Level</h6>
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="star-rating-picker">
                        <?php $cherish = (int) ($ct['cherish_rating'] ?? 0); ?>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="cherish_<?= $i ?>" name="cherish_rating" value="<?= $i ?>" <?= $cherish === $i ? 'checked' : '' ?>>
                            <label for="cherish_<?= $i ?>" title="<?= $i ?> star<?= $i > 1 ? 's' : '' ?>"><i class="fa-solid fa-star"></i></label>
                        <?php endfor; ?>
                    </div>
                    <button type="button" class="btn btn-sm btn-link text-muted p-0" onclick="CollectionWizard.clearCherishRating()">Clear</button>
                    <span class="small text-muted ms-auto">How much do you personally love this one? Not the same as condition or value.</span>
                </div>
            </div>
        </div>

        <style>
            .star-rating-picker { display: inline-flex; flex-direction: row-reverse; gap: 2px; }
            .star-rating-picker input { position: absolute; opacity: 0; pointer-events: none; }
            .star-rating-picker label { cursor: pointer; font-size: 1.5rem; line-height: 1; color: #dee2e6; }
            .star-rating-picker input:checked ~ label,
            .star-rating-picker label:hover,
            .star-rating-picker label:hover ~ label { color: #f0ad4e; }
        </style>

        <!-- Purchase Information -->
        <div>
            <h6 class="text-uppercase fw-bold text-muted small mb-2">Purchase Information</h6>
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1">Date Acquired</label>
                            <input type="date" class="form-control" name="date_acquired" value="<?= $e($ct['date_acquired'] ?? date('Y-m-d')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1">
                                Purchase Price <span class="text-muted fw-normal">(<a href="<?= $baseUrl ?>settings" target="_blank" class="text-muted">change currency</a>)</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white text-muted"><?= $e($currency) ?></span>
                                <input type="number" step="0.01" class="form-control" name="purchase_price" placeholder="0.00" value="<?= $e($ct['purchase_price'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1">Current Value</label>
                            <input type="number" step="0.01" class="form-control" name="current_value" placeholder="0.00" value="<?= $e($ct['current_value'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted mb-1">Source</label>
                            <select class="form-select" name="purchase_source_id">
                                <option value="">Unknown / Other</option>
                                <?php foreach($sources as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= ($ct['purchase_source_id'] ?? '') == $s['id'] ? 'selected' : '' ?>><?= $e($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted mb-1">Acquisition Status</label>
                            <select class="form-select" name="acquisition_status_id">
                                <option value="">Select Status...</option>
                                <?php foreach($acquisitionStatuses as $st): ?>
                                    <option value="<?= $st['id'] ?>" <?= ($ct['acquisition_status_id'] ?? '') == $st['id'] ? 'selected' : '' ?>><?= $e($st['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Condition & Storage -->
        <div>
            <h6 class="text-uppercase fw-bold text-muted small mb-2">Condition & Storage</h6>
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1">Packaging</label>
                            <select class="form-select" name="packaging_type_id">
                                <option value="">Select...</option>
                                <?php foreach($packagingTypes as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= ($ct['packaging_type_id'] ?? '') == $p['id'] ? 'selected' : '' ?>><?= $e($p['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1">Condition Grade</label>
                            <select class="form-select" name="condition_grade_id">
                                <option value="">Not Graded</option>
                                <?php foreach($conditionGrades as $cg): ?>
                                    <option value="<?= $cg['id'] ?>" <?= ($ct['condition_grade_id'] ?? '') == $cg['id'] ? 'selected' : '' ?>>
                                        <?= $e($cg['name']) ?><?= !empty($cg['abbreviation']) ? ' (' . $e($cg['abbreviation']) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1">Storage Unit</label>
                            <select class="form-select" name="storage_unit_id">
                                <option value="">Unsorted</option>
                                <?php foreach($storageUnits as $su): ?>
                                    <option value="<?= $su['id'] ?>" <?= ($ct['storage_unit_id'] ?? '') == $su['id'] ? 'selected' : '' ?>>
                                        <?= $e($su['box_code'] ? $su['box_code'] . ' - ' . $su['name'] : $su['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grading (collapsible) -->
        <div>
            <h6 class="text-uppercase fw-bold text-muted small mb-2">
                <a class="text-decoration-none text-muted" data-bs-toggle="collapse" href="#gradingSection" role="button" aria-expanded="false">
                    <i class="fa-solid fa-chevron-right me-1 small" id="gradingChevron"></i> Professional Grading
                </a>
            </h6>
            <div class="collapse <?= !empty($ct['grader_company_id']) ? 'show' : '' ?>" id="gradingSection">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-1">Grading Company</label>
                                <select class="form-select" name="grader_company_id">
                                    <option value="">None</option>
                                    <?php foreach($gradingCompanies as $gc): ?>
                                        <option value="<?= $gc['id'] ?>" <?= ($ct['grader_company_id'] ?? '') == $gc['id'] ? 'selected' : '' ?>><?= $e($gc['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-1">Grader Tier</label>
                                <select class="form-select" name="grader_tier_id">
                                    <option value="">N/A</option>
                                    <?php foreach($graderTiers as $gt): ?>
                                        <option value="<?= $gt['id'] ?>" <?= ($ct['grader_tier_id'] ?? '') == $gt['id'] ? 'selected' : '' ?>><?= $e($gt['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-1">Grade Serial #</label>
                                <input type="text" class="form-control" name="grade_serial" value="<?= $e($ct['grade_serial'] ?? '') ?>" placeholder="e.g. AFA-12345">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-1">Grade Score</label>
                                <input type="text" class="form-control" name="grade_score" value="<?= $e($ct['grade_score'] ?? '') ?>" placeholder="e.g. 85">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div>
            <h6 class="text-uppercase fw-bold text-muted small mb-2">Notes</h6>
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <textarea class="form-control" name="notes" rows="2" placeholder="Any notes about this collection entry..."><?= $e($ct['notes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Included Items -->
        <?php if(!empty($catalogItems)): ?>
        <div>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="text-uppercase fw-bold text-muted small mb-0">Included Items</h6>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-dark btn-sm" onclick="CollectionWizard.checkAllItems(true)">
                        <i class="fa-solid fa-check-double me-1"></i> All Present
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="CollectionWizard.checkAllItems(false)">
                        Clear
                    </button>
                </div>
            </div>

            <div class="vstack gap-2" id="itemsContainer">
                <?php foreach($catalogItems as $idx => $catItem): ?>
                    <?php
                        $ci = $itemMap[$catItem['id']] ?? null;
                        $isPresent = $ci ? (bool)$ci['is_present'] : !$isEdit;
                        $isRepro = $ci ? (bool)$ci['is_repro'] : false;
                        $hasItemOverrides = $ci && ($ci['acquisition_status_id'] || $ci['packaging_type_id'] || $ci['storage_unit_id'] || $ci['condition_notes']);
                    ?>
                    <div class="card shadow-sm border-0 collection-item-row">
                        <div class="card-body p-3">
                            <?php if($ci): ?>
                                <input type="hidden" name="items[<?= $idx ?>][id]" value="<?= $ci['id'] ?>">
                            <?php endif; ?>
                            <input type="hidden" name="items[<?= $idx ?>][catalog_toy_item_id]" value="<?= $catItem['id'] ?>">

                            <div class="d-flex align-items-center gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="items[<?= $idx ?>][is_present]" value="1"
                                           id="item_present_<?= $idx ?>" <?= $isPresent ? 'checked' : '' ?>>
                                </div>
                                <?php if(!empty($catItem['image_path'])): ?>
                                    <img src="<?= $e($catItem['image_path']) ?>" title="Catalog default photo" class="rounded border flex-shrink-0"
                                         style="width: 52px; height: 52px; object-fit: contain; background: #f8f9fa;">
                                <?php endif; ?>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold small"><?= $e($catItem['subject_name']) ?></span>
                                        <span class="badge bg-secondary" style="font-size: 0.6rem;"><?= $e($catItem['subject_type']) ?></span>
                                    </div>
                                    <?php if(!empty($catItem['description'])): ?>
                                        <div class="text-muted" style="font-size: 0.75rem;"><?= $e($catItem['description']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex align-items-center gap-3 flex-shrink-0">
                                    <div class="form-check form-check-inline mb-0">
                                        <input class="form-check-input" type="checkbox" name="items[<?= $idx ?>][is_repro]" value="1"
                                               id="item_repro_<?= $idx ?>" <?= $isRepro ? 'checked' : '' ?>>
                                        <label class="form-check-label small text-muted" for="item_repro_<?= $idx ?>">Repro</label>
                                    </div>
                                    <select class="form-select form-select-sm" name="items[<?= $idx ?>][condition_grade_id]" style="width: 130px;">
                                        <option value="">Condition...</option>
                                        <?php foreach($conditionGrades as $cg): ?>
                                            <option value="<?= $cg['id'] ?>" <?= ($ci['condition_grade_id'] ?? '') == $cg['id'] ? 'selected' : '' ?>>
                                                <?= $e($cg['abbreviation'] ?: $cg['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Expandable per-item overrides -->
                            <div class="text-center mt-2">
                                <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 text-muted small"
                                        data-bs-toggle="collapse" data-bs-target="#item_details_<?= $idx ?>">
                                    <i class="fa-solid fa-chevron-down me-1"></i> Item overrides (status, packaging, storage, notes)
                                </button>
                            </div>
                            <div class="collapse <?= $hasItemOverrides ? 'show' : '' ?> mt-3 pt-3 border-top" id="item_details_<?= $idx ?>">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted mb-0">Acquisition Status</label>
                                        <select class="form-select form-select-sm" name="items[<?= $idx ?>][acquisition_status_id]">
                                            <option value="">Use parent value</option>
                                            <?php foreach($acquisitionStatuses as $st): ?>
                                                <option value="<?= $st['id'] ?>" <?= ($ci['acquisition_status_id'] ?? '') == $st['id'] ? 'selected' : '' ?>><?= $e($st['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted mb-0">Packaging</label>
                                        <select class="form-select form-select-sm" name="items[<?= $idx ?>][packaging_type_id]">
                                            <option value="">Use parent value</option>
                                            <?php foreach($packagingTypes as $p): ?>
                                                <option value="<?= $p['id'] ?>" <?= ($ci['packaging_type_id'] ?? '') == $p['id'] ? 'selected' : '' ?>><?= $e($p['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted mb-0">Storage Unit</label>
                                        <select class="form-select form-select-sm" name="items[<?= $idx ?>][storage_unit_id]">
                                            <option value="">Use parent value</option>
                                            <?php foreach($storageUnits as $su): ?>
                                                <option value="<?= $su['id'] ?>" <?= ($ci['storage_unit_id'] ?? '') == $su['id'] ? 'selected' : '' ?>>
                                                    <?= $e($su['box_code'] ? $su['box_code'] . ' - ' . $su['name'] : $su['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted mb-0">Condition Notes</label>
                                        <input type="text" class="form-control form-control-sm" name="items[<?= $idx ?>][condition_notes]"
                                               value="<?= $e($ci['condition_notes'] ?? '') ?>" placeholder="e.g. Small paint chip on left arm">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <div class="modal-footer bg-light justify-content-between border-top flex-shrink-0">
        <button type="button" class="btn btn-link text-muted text-decoration-none"
                <?= $isEdit ? 'data-bs-dismiss="modal"' : 'onclick="CollectionWizard.loadStep1()"' ?>>
            <i class="fa-solid fa-arrow-left me-1"></i> <?= $isEdit ? 'Cancel' : 'Back' ?>
        </button>
        <button type="submit" class="btn btn-dark px-4">
            <?= $isEdit ? 'Save Changes' : 'Save & Add Photos <i class="fa-solid fa-arrow-right ms-1"></i>' ?>
        </button>
    </div>
</form>

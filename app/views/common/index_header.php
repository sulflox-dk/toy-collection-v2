<?php
// Defaults
$entityKey = $entityKey ?? 'entity';
$searchPlaceholder = $searchPlaceholder ?? 'Search...';
$extraFilters = $extraFilters ?? ''; // <--- THE CUSTOM SLOT
$showSearch = $showSearch ?? true;   // <--- Toggle Standard Search
$showVisibility = $showVisibility ?? true; // <--- Toggle Standard Visibility
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800"><?= $e($title) ?></h1>
        <?php if(!empty($subtitle)): ?>
            <p class="text-muted small mb-0"><?= $e($subtitle) ?></p>
        <?php endif; ?>
    </div>
    <button id="btn-add-<?= $e($entityKey) ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus me-2"></i> <?= $e($addBtnText) ?>
    </button>
</div>

<div class="row mb-3 gx-2">
    
    <div class="col-md-2">
        <?php if($showSearch): ?>
        <div class="col-md-auto mb-2 mb-md-0">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                <input type="text" class="form-control border-start-0 ps-1" id="search-input" placeholder="<?= $e($searchPlaceholder) ?>">
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-md-9">
        <div class="row mb-3 gx-2 align-items-center row-gap-2">
            <?= $extraFilters ?>
        </div>
    </div>

    <div class="col-md-1">
        
        <div class="row mb-3 gx-2 align-items-center row-gap-2">
            
            <div class="col-md-auto ms-auto">
                <button class="btn btn-light border" id="btn-reset-filters" title="Reset Filters">
                    <i class="fa-solid fa-rotate-left text-muted"></i>
                </button>
            </div>

            <?php if(isset($showViewMode) && $showViewMode): ?>
            <div class="col-md-auto ms-auto d-flex align-items-center">
                <input type="hidden" class="data-filter" name="view" id="view-mode-input" value="list" data-entity="<?= $e($entityKey) ?>">
                <div class="btn-group shadow-sm">
                    <button type="button" class="btn btn-light border active" id="btn-view-list" onclick="setViewMode('list')" title="List View">
                        <i class="fa-solid fa-list text-muted"></i>
                    </button>
                    <button type="button" class="btn btn-light border" id="btn-view-cards" onclick="setViewMode('cards')" title="Grid View">
                        <i class="fa-solid fa-th text-muted"></i>
                    </button>
                </div>
            </div>
            <?php endif; ?>

        </div>

    </div>

</div>

<div class="card border-0">
    <div class="card-body p-0">
        <div id="<?= $e($entityKey) ?>-grid"></div>
    </div>
</div>
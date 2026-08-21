<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800"><?= $e($title) ?></h1>
        <p class="text-muted small mb-0">Every part missing from your collection, in one list — instead of clicking into each toy to find out.</p>
    </div>
</div>

<div class="row mb-3 gx-2 row-gap-2">
    <div class="col-md-3">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
            <input type="text" class="form-control border-start-0 ps-1" id="mp-search-part" placeholder="Search part name...">
        </div>
    </div>
    <div class="col-md-3">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-cube text-muted"></i></span>
            <input type="text" class="form-control border-start-0 ps-1" id="mp-search-toy" placeholder="Search toy name...">
        </div>
    </div>
    <div class="col-md-2">
        <select class="form-select" id="mp-filter-universe">
            <option value="">All Universes</option>
            <?php foreach ($universes as $u): ?>
                <option value="<?= $e($u['id']) ?>"><?= $e($u['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <select class="form-select" id="mp-filter-manufacturer">
            <option value="">All Manufacturers</option>
            <?php foreach ($manufacturers as $m): ?>
                <option value="<?= $e($m['id']) ?>"><?= $e($m['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <select class="form-select" id="mp-filter-toy-line">
            <option value="">All Toy Lines</option>
            <?php foreach ($toyLines as $tl): ?>
                <option value="<?= $e($tl['id']) ?>"><?= $e($tl['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="row mb-3 gx-2 align-items-center row-gap-2">
    <div class="col-md-2">
        <select class="form-select" id="mp-filter-product-type">
            <option value="">All Product Types</option>
            <?php foreach ($productTypes as $pt): ?>
                <option value="<?= $e($pt['id']) ?>"><?= $e($pt['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <select class="form-select" id="mp-sort">
            <option value="part">Sort: Part name</option>
            <option value="toy">Sort: Toy name</option>
            <option value="cherish">Sort: Cherish level</option>
        </select>
    </div>
    <div class="col-md-auto">
        <div class="form-check form-switch pt-2">
            <input class="form-check-input" type="checkbox" id="mp-show-repro">
            <label class="form-check-label small" for="mp-show-repro">Also show reproduction parts</label>
        </div>
    </div>
    <div class="col-md-auto ms-auto">
        <button class="btn btn-light border" id="mp-reset-filters" title="Reset Filters">
            <i class="fa-solid fa-rotate-left text-muted"></i>
        </button>
    </div>
</div>

<div class="card border-0">
    <div class="card-body p-0">
        <div id="missing-part-grid"></div>
    </div>
</div>

<!-- Shared wizard modal shell (populated by CollectionWizard, from collection_toys.js) -->
<div class="modal fade" id="entity-modal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-vw-85 modal-dialog-centered">
        <div class="modal-content" id="entity-modal-content"></div>
    </div>
</div>

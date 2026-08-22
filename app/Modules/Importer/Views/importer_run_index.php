<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Importer / Run Import</h1>
        <p class="text-muted small mb-0">Paste a URL from a supported source to import catalog data. Add more than one URL for the same toy to combine data from several sites.</p>
    </div>
</div>

<?php if (!empty($stats)): ?>
<div class="row mb-4">
    <?php foreach ($stats as $stat): ?>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold"><?= $e($stat['name']) ?></div>
                        <div class="h4 mb-0 mt-1"><?= (int) $stat['imported_count'] ?></div>
                        <div class="text-muted small">items imported</div>
                    </div>
                    <div class="ms-3">
                        <?php if ($stat['is_active']): ?>
                            <span class="badge bg-success-subtle text-success"><i class="fa-solid fa-circle fa-xs me-1"></i> Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary"><i class="fa-solid fa-circle fa-xs me-1"></i> Inactive</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php if ($stat['last_activity']): ?>
            <div class="card-footer bg-transparent border-top-0 pt-0">
                <small class="text-muted">Last: <?= $e($stat['last_activity']) ?></small>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <i class="fa-solid fa-cloud-download-alt me-2 text-primary"></i>
        <strong>Add Sources</strong>
    </div>
    <div class="card-body">
        <?= $csrfField() ?>
        <div class="row g-2">
            <div class="col-md-7">
                <input type="text" id="importUrl" class="form-control form-control-lg" placeholder="Paste a URL here (listing page or single detail page)">
            </div>
            <div class="col-md-2">
                <input type="number" min="0" step="20" class="form-control form-control-lg" id="importOffset" value="0" title="Offset (for listing pages with more than 20 items)">
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary btn-lg w-100" type="button" id="btnAddSource">
                    <i class="fa-solid fa-plus me-2"></i> Add
                </button>
            </div>
        </div>
        <div class="form-text">
            A single detail page adds one item below. A listing page adds up to 20 at once (use Offset to fetch the
            next 20, and so on). Each item below is its own toy — use "Add source" on any of them afterward to
            combine in data from another site for that specific toy.
        </div>
        <div id="addSourceStatus" class="small ms-1 mt-1"></div>

        <div class="row g-2 align-items-end mt-2 pt-2 border-top">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Default Universe</label>
                <select class="form-select form-select-sm" id="batchUniverse">
                    <option value="">None</option>
                    <?php foreach ($universes as $u): ?>
                        <option value="<?= $e($u['id']) ?>"><?= $e($u['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Default Manufacturer</label>
                <select class="form-select form-select-sm" id="batchManufacturer">
                    <option value="">None</option>
                    <?php foreach ($manufacturers as $m): ?>
                        <option value="<?= $e($m['id']) ?>"><?= $e($m['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Default Toy Line</label>
                <select class="form-select form-select-sm" id="batchToyLine">
                    <option value="">None</option>
                    <?php foreach ($toyLines as $tl): ?>
                        <option value="<?= $e($tl['id']) ?>">
                            <?= $e($tl['name']) ?><?= $tl['manufacturer_name'] ? ' (' . $e($tl['manufacturer_name']) . ')' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Default Product Type</label>
                <select class="form-select form-select-sm" id="batchProductType">
                    <option value="">None</option>
                    <?php foreach ($productTypes as $pt): ?>
                        <option value="<?= $e($pt['id']) ?>"><?= $e($pt['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Default Entertainment Source</label>
                <select class="form-select form-select-sm" id="batchEntertainmentSource">
                    <option value="">None</option>
                    <?php foreach ($entertainmentSources as $es): ?>
                        <option value="<?= $e($es['id']) ?>"><?= $e($es['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button class="btn btn-outline-secondary btn-sm w-100" type="button" id="btnResetBatchDefaults">
                    <i class="fa-solid fa-rotate-left me-1"></i> Reset defaults
                </button>
            </div>
        </div>
        <div class="form-text">
            Set these when you're about to import a batch that's mostly the same (e.g. "this whole session is
            Hasbro Vintage Collection") — they're remembered across visits and applied to everything you add,
            <strong>unless</strong> the importer finds a more specific value on the page itself, which always wins.
            Still editable per item below.
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Import Queue <span id="itemCount" class="badge bg-secondary ms-1"><?= 0 ?></span></h4>
    <div>
        <button id="btnSelectAll" class="btn btn-outline-secondary btn-sm me-2">
            <i class="fa-solid fa-check-double me-1"></i> Select All
        </button>
        <button id="btnDeselectAll" class="btn btn-outline-secondary btn-sm me-2">
            <i class="fa-solid fa-xmark me-1"></i> Deselect All
        </button>
        <button id="btnRunImport" class="btn btn-success">
            <i class="fa-solid fa-file-import me-2"></i> Import Selected
        </button>
    </div>
</div>

<div id="importQueueEmpty" class="alert alert-light border text-muted text-center py-4">
    <i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>
    Nothing queued yet — add a source above to get started.
</div>
<div id="importQueue"></div>

<script>
    // Lookup lists for the per-item editable fields, and the group-size cap.
    const IMPORTER_LOOKUPS = <?= json_encode([
        'universes' => array_map(fn($u) => ['id' => (int) $u['id'], 'name' => $u['name']], $universes),
        'manufacturers' => array_map(fn($m) => ['id' => (int) $m['id'], 'name' => $m['name']], $manufacturers),
        'toyLines' => array_map(fn($tl) => [
            'id' => (int) $tl['id'],
            'name' => $tl['name'] . ($tl['manufacturer_name'] ? ' (' . $tl['manufacturer_name'] . ')' : ''),
        ], $toyLines),
        'productTypes' => array_map(fn($pt) => ['id' => (int) $pt['id'], 'name' => $pt['name']], $productTypes),
        'entertainmentSources' => array_map(fn($es) => ['id' => (int) $es['id'], 'name' => $es['name']], $entertainmentSources),
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const IMPORTER_MAX_SOURCES_PER_GROUP = <?= (int) $maxSourcesPerGroup ?>;
</script>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Importer / Run Import</h1>
        <p class="text-muted small mb-0">Paste a URL from a supported source to import catalog data.</p>
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
        <strong>Analyze URL</strong>
    </div>
    <div class="card-body">
        <form id="importForm" onsubmit="return false;">
            <?= $csrfField() ?>
            <div class="input-group input-group-lg mb-2">
                <input type="text" id="importUrl" class="form-control" placeholder="Paste URL here (overview or single detail page)" required>
                <button class="btn btn-primary" type="button" id="btnPreview">
                    <i class="fa-solid fa-search me-2"></i> Analyze
                </button>
            </div>
            <div class="form-text mb-3">Paste a URL from any active source. Overview pages import multiple items; detail pages import a single item.</div>

            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Batch Universe</label>
                    <select class="form-select form-select-sm" id="batchUniverse">
                        <option value="">Auto-detect / none</option>
                        <?php foreach ($universes as $u): ?>
                            <option value="<?= $e($u['id']) ?>"><?= $e($u['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Batch Manufacturer</label>
                    <select class="form-select form-select-sm" id="batchManufacturer">
                        <option value="">Auto-detect from page</option>
                        <?php foreach ($manufacturers as $m): ?>
                            <option value="<?= $e($m['id']) ?>"><?= $e($m['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Batch Toy Line</label>
                    <select class="form-select form-select-sm" id="batchToyLine">
                        <option value="">Auto-detect from page</option>
                        <?php foreach ($toyLines as $tl): ?>
                            <option value="<?= $e($tl['id']) ?>">
                                <?= $e($tl['name']) ?><?= $tl['manufacturer_name'] ? ' (' . $e($tl['manufacturer_name']) . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Offset</label>
                    <input type="number" min="0" step="20" class="form-control form-control-sm" id="importOffset" value="0">
                </div>
                <div class="col-md-1">
                    <span class="text-muted small" id="offsetInfo"></span>
                </div>
            </div>
            <div class="form-text">
                Universe/Manufacturer/Toy Line set here apply to every item found — leave blank to let each
                toy's own value (from the page, if detected) be used instead. Both are still editable per-item
                below before you import. Offset only matters for listing pages with more than 20 items: run
                Analyze again with Offset 20, then 40, and so on to work through the rest.
            </div>
        </form>
    </div>
</div>

<div id="importResults" class="d-none">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Found Items <span id="itemCount" class="badge bg-secondary ms-1">0</span></h4>
            <small class="text-muted" id="sourceName"></small>
        </div>
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

    <div id="resultsGrid"></div>
</div>

<script>
    // Lookup lists for the per-item editable fields in the preview grid.
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
</script>

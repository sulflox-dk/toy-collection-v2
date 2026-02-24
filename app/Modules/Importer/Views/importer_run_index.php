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
            <div class="input-group input-group-lg">
                <input type="text" id="importUrl" class="form-control" placeholder="Paste URL here (overview or single detail page)" required>
                <button class="btn btn-primary" type="button" id="btnPreview">
                    <i class="fa-solid fa-search me-2"></i> Analyze
                </button>
            </div>
            <div class="form-text">Paste a URL from any active source. Overview pages import multiple items; detail pages import a single item.</div>
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

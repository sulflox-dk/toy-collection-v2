<?php
ob_start();
?>
<div class="col-md-3 mb-2 mb-md-0">
    <select class="form-select data-filter" name="source_id">
        <option value="">All Sources</option>
        <?php foreach ($sources as $source): ?>
            <option value="<?= $e($source['id']) ?>"><?= $e($source['name']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="col-md-3 mb-2 mb-md-0">
    <select class="form-select data-filter" name="status">
        <option value="">All Statuses</option>
        <option value="Success">Success</option>
        <option value="Warning">Warning</option>
        <option value="Error">Error</option>
    </select>
</div>
<?php
$customFilters = ob_get_clean();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Importer / Logs</h1>
        <p class="text-muted small mb-0">Import activity and error log.</p>
    </div>
</div>

<div class="row mb-3 gx-2">
    <div class="col-md-2">
        <div class="col-md-auto mb-2 mb-md-0">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                <input type="text" class="form-control border-start-0 ps-1" id="search-input" placeholder="Search...">
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="row mb-3 gx-2 align-items-center row-gap-2">
            <?= $customFilters ?>
        </div>
    </div>
    <div class="col-md-1">
        <div class="row mb-3 gx-2 align-items-center row-gap-2">
            <div class="col-md-auto ms-auto">
                <button class="btn btn-light border" id="btn-reset-filters" title="Reset Filters">
                    <i class="fa-solid fa-rotate-left text-muted"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card border-0">
    <div class="card-body p-0">
        <div id="importer-log-grid"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    new EntityManager('importer-log', {
        mode: 'html',
        endpoint: '/importer-log',
        listUrl: '/importer-log/list',
        canCreate: false,
        canEdit: false,
        canDelete: false,
    });
});
</script>

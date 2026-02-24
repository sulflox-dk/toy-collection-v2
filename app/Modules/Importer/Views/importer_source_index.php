<?php
ob_start();
?>
<div class="col-md-3 mb-2 mb-md-0">
    <select class="form-select data-filter" name="active">
        <option value="">All Statuses</option>
        <option value="1">Active</option>
        <option value="0">Inactive</option>
    </select>
</div>
<?php
$customFilters = ob_get_clean();

echo $this->renderPartial('common/index_header', [
    'title' => 'Importer / Sources',
    'subtitle' => 'Manage external data sources for catalog imports.',
    'entityKey' => 'importer-source',
    'addBtnText' => 'Add Source',
    'extraFilters' => $customFilters
]);
?>

<div class="modal fade" id="importer-source-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Source</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="importer-source-form">
                <div class="modal-body">
                    <?= $csrfField() ?>
                    <input type="hidden" name="id">

                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required placeholder="e.g. Galactic Figures">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control" name="slug" placeholder="Auto-generated if empty">
                        <div class="invalid-feedback"></div>
                        <div class="form-text text-muted small">Unique ID. Leave empty to auto-generate.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Base URL</label>
                        <input type="text" class="form-control" name="base_url" required placeholder="e.g. galacticfigures.com">
                        <div class="invalid-feedback"></div>
                        <div class="form-text text-muted small">Domain used to match pasted URLs to this source.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Driver Class</label>
                        <select class="form-select" name="driver_class" required>
                            <option value="">-- Select Driver --</option>
                            <option value="App\Modules\Importer\Drivers\GalacticFiguresDriver">GalacticFiguresDriver</option>
                            <option value="App\Modules\Importer\Drivers\JediTempleArchivesDriver">JediTempleArchivesDriver</option>
                            <option value="App\Modules\Importer\Drivers\ActionFigure411Driver">ActionFigure411Driver</option>
                            <option value="App\Modules\Importer\Drivers\GalacticCollectorDriver">GalacticCollectorDriver</option>
                            <option value="App\Modules\Importer\Drivers\StarWarsCollectorDriver">StarWarsCollectorDriver</option>
                            <option value="App\Modules\Importer\Drivers\TheToyCollectorsGuideDriver">TheToyCollectorsGuideDriver</option>
                        </select>
                        <div class="invalid-feedback"></div>
                        <div class="form-text text-muted small">The PHP driver class that handles scraping for this source.</div>
                    </div>

                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-link me-auto" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-secondary d-none" data-action="add-another">Save and Add Another</button>
                    <button type="submit" class="btn btn-primary" data-action="save-close">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    new EntityManager('importer-source', {
        mode: 'html',
        endpoint: '/importer-source',
        listUrl: '/importer-source/list',
    });
});
</script>

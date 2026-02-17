<?php
// 1. Build Custom Filters
ob_start(); 
?>
<div class="col-md-2 mb-2 mb-md-0">
    <select class="form-select data-filter" name="type">
        <option value="">All Types</option>
        <?php foreach ($types as $type): ?>
            <option value="<?= $type ?>"><?= $e($type) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div class="col-md-2 mb-2 mb-md-0">
    <select class="form-select data-filter" name="universe_id">
        <option value="">All Universes</option>
        <?php foreach ($universes as $u): ?>
            <option value="<?= $u['id'] ?>"><?= $e($u['name']) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div class="col-md-2 mb-2 mb-md-0">
    <select class="form-select data-filter" name="visibility">
        <option value="">All Status</option>
        <option value="1">Visible</option>
        <option value="0">Hidden</option>
    </select>
</div>
<?php 
$customFilters = ob_get_clean();

// 2. Render Generic Header
echo $this->renderPartial('common/index_header', [
    'title' => 'Meta / Entertainment Sources',
    'subtitle' => 'Movies, TV shows, Games, and Books.',
    'entityKey' => 'source', // Generates #btn-add-source and #source-grid
    'addBtnText' => 'Add Source',
    'extraFilters' => $customFilters
]);
?>

<div class="modal fade" id="source-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Entertainment Source</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="source-form">
                <div class="modal-body">
                    <?= $csrfField() ?>
                    <input type="hidden" name="id">
                    
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required placeholder="e.g. A New Hope">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="type">
                                <?php foreach ($types as $type): ?>
                                    <option value="<?= $type ?>"><?= $type ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Release Year</label>
                            <input type="number" class="form-control" name="release_year" placeholder="YYYY" min="1900" max="2100">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Universe</label>
                        <select class="form-select" name="universe_id" required>
                            <option value="">Select...</option>
                            <?php foreach ($universes as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= $e($u['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="Optional details..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control" name="slug" placeholder="Auto-generated if empty">
                        <div class="invalid-feedback">Error message will appear here</div>
                    </div>

                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="show_on_dashboard" id="show_on_dashboard" value="1">
                        <label class="form-check-label" for="show_on_dashboard">Show on Dashboard</label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-link me-auto" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" data-action="save-close">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // FIX: Moved UI selectors into the 'ui' object
    new EntityManager('source', {
        mode: 'html',
        endpoint: '/entertainment-source',
        listUrl: '/entertainment-source/list',
        ui: {
            modalId: 'source-modal',
            formId: 'source-form',
            gridId: 'source-grid',
            btnAddId: 'btn-add-source'
        }
    });
});
</script>
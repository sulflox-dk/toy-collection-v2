<?php
// 1. Build the HTML for your custom filters
ob_start(); 
?>
<div class="col-md-3 mb-2 mb-md-0">
    <select class="form-select data-filter" name="manufacturer_id">
        <option value="">All Manufacturers</option>
        <?php foreach ($manufacturers as $m): ?>
            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="col-md-3 mb-2 mb-md-0">
    <select class="form-select data-filter" name="universe_id">
        <option value="">All Universes</option>
        <?php foreach ($universes as $u): ?>
            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="col-md-3 mb-2 mb-md-0">
    <select class="form-select data-filter" name="visibility">
        <option value="">All Status</option>
        <option value="1">Visible</option>
        <option value="0">Hidden</option>
    </select>
</div>
<?php 
$customFiltersHTML = ob_get_clean();

// 2. Pass it to the generic header
echo $this->renderPartial('common/index_header', [
    'title' => 'Meta / Toy Lines',
    'subtitle' => 'Manage product lines (e.g. Vintage Collection, Classified Series).',
    'entityKey' => 'toy-line',
    'addBtnText' => 'Add Toy Line',
    
    // Inject the HTML we just built
    'extraFilters' => $customFiltersHTML, 
    
    // Toggle standard stuff if you want
    'showVisibility' => true 
]);
?>

<div class="modal fade" id="toy-line-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Toy Line</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="toy-line-form">
                <div class="modal-body">
                    <?= $csrfField() ?>
                    <input type="hidden" name="id">
                    
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required placeholder="e.g. The Vintage Collection">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Manufacturer</label>
                            <select class="form-select" name="manufacturer_id" required>
                                <option value="">Select...</option>
                                <?php foreach ($manufacturers as $m): ?>
                                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Universe</label>
                            <select class="form-select" name="universe_id" required>
                                <option value="">Select...</option>
                                <?php foreach ($universes as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control" name="slug" placeholder="Auto-generated if empty">
                        <div class="invalid-feedback">Error message will appear here</div>
                        <div class="form-text text-muted small">Unique ID for URLs. Leave empty to auto-generate.</div>
                    </div>

                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="show_on_dashboard" id="show_on_dashboard" value="1">
                        <label class="form-check-label" for="show_on_dashboard">Show on Dashboard</label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-link me-auto" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-secondary d-none" data-action="add-another">
                        Save and Add Another
                    </button>
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
    new EntityManager('toy-line', {
        mode: 'html',
        endpoint: '/toy-line',
        listUrl: '/toy-line/list',
    });
});
</script>
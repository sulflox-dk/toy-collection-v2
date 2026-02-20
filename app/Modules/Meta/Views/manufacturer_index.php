<?php
// 1. Build Custom Filters (Visibility Only)
ob_start(); 
?>
<div class="col-md-3 mb-2 mb-md-0">
    <select class="form-select data-filter" name="visibility">
        <option value="">All Visibilities</option>
        <option value="1">Visible on Dashboard</option>
        <option value="0">Hidden from Dashboard</option>
    </select>
</div>
<?php 
$customFilters = ob_get_clean();

// 2. Render Generic Header
echo $this->renderPartial('common/index_header', [
    'title' => 'Meta / Manufacturers',
    'subtitle' => 'Manage the companies that make the toys.',
    'entityKey' => 'manufacturer',
    'addBtnText' => 'Add Manufacturer',
    'extraFilters' => $customFilters
]);
?>

<div class="modal fade" id="manufacturer-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manufacturer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="manufacturer-form">
                <div class="modal-body">
                    <?= $csrfField() ?>
                    <input type="hidden" name="id">
                    
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required placeholder="e.g. Kenner">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control" name="slug" placeholder="Auto-generated if empty (e.g. kenner)">
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
    new EntityManager('manufacturer', {
        mode: 'html',
        endpoint: '/manufacturer',
        listUrl: '/manufacturer/list',
    });
});
</script>
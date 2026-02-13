<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Meta / Manufacturers</h1>
        <p class="text-muted small mb-0">Manage the companies that make the toys.</p>
    </div>
    <button id="btn-add-manufacturer" class="btn btn-primary">
        <i class="fa-solid fa-plus me-2"></i> Add Manufacturer
    </button>
</div>

<div class="row mb-3 align-items-center">
    <div class="col-md-4 mb-2 mb-md-0">
        <div class="input-group">
            <span class="input-group-text bg-white"><i class="fa-solid fa-search text-muted"></i></span>
            <input type="text" class="form-control" id="search-input" placeholder="Search manufacturers...">
        </div>
    </div>
    <div class="col-md-3 mb-2 mb-md-0">
        <select class="form-select data-filter" name="visibility">
            <option value="">All Visibilities</option>
            <option value="1">Visible on Dashboard</option>
            <option value="0">Hidden from Dashboard</option>
        </select>
    </div>
    <div class="col-md-auto ms-auto">
        <button class="btn btn-outline-secondary btn-sm" id="btn-reset-filters">
            <i class="fa-solid fa-rotate-left me-1"></i> Reset
        </button>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div id="manufacturer-grid"></div>
    </div>
</div>

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
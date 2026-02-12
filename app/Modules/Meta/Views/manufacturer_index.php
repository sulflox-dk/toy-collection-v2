<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Meta / Manufacturers</h1>
        <p class="text-muted small mb-0">Manage the companies that make the toys.</p>
    </div>
    <button id="btn-add-manufacturer" class="btn btn-primary">
        <i class="fa-solid fa-plus me-2"></i> Add Manufacturer
    </button>
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
            <div class="modal-body">
                <form id="manufacturer-form">
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

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="show_on_dashboard" id="show_on_dashboard" value="1">
                        <label class="form-check-label" for="show_on_dashboard">Show on Dashboard</label>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
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
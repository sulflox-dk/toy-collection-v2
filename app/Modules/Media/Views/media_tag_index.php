<?php
// Render Generic Header
echo $this->renderPartial('common/index_header', [
    'title' => 'Media / Tags',
    'subtitle' => 'Manage tags to organize your media files.',
    'entityKey' => 'media-tag',
    'addBtnText' => 'Add Tag',
    'showVisibility' => false // Vi har ikke "show_on_dashboard" her
]);
?>

<div class="modal fade" id="media-tag-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Media Tag</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="media-tag-form">
                <div class="modal-body">
                    <?= $csrfField() ?>
                    <input type="hidden" name="id">
                    
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required placeholder="e.g. Action Figure">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control" name="slug" placeholder="Auto-generated if empty">
                        <div class="invalid-feedback">Error message will appear here</div>
                        <div class="form-text text-muted small">Unique ID for URLs. Leave empty to auto-generate.</div>
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
    new EntityManager('media-tag', {
        mode: 'html',
        endpoint: '/media-tag',
        listUrl: '/media-tag/list',
    });
});
</script>
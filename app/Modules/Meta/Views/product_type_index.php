<?php
// No custom filters needed here since we removed visibility/dashboard logic
echo $this->renderPartial('common/index_header', [
    'title' => 'Meta / Product Types',
    'subtitle' => 'Categories of items (e.g. Action Figure, Vehicle, Playset).',
    'entityKey' => 'product-type',
    'addBtnText' => 'Add Product Type',
    'showVisibility' => false // <--- Disable the standard visibility filter
]);
?>

<div class="modal fade" id="product-type-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Product Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="product-type-form">
                <div class="modal-body">
                    <?= $csrfField() ?>
                    <input type="hidden" name="id">
                    
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required placeholder="e.g. Action Figure">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="Optional description..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control" name="slug" placeholder="Auto-generated if empty">
                        <div class="invalid-feedback">Error message will appear here</div>
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
    new EntityManager('product-type', {
        mode: 'html',
        endpoint: '/product-type',
        listUrl: '/product-type/list',
    });
});
</script>
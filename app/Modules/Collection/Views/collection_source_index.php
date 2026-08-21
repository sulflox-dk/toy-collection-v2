<?php
echo $this->renderPartial('common/index_header', [
    'title' => 'Collection / Purchase Sources',
    'subtitle' => 'Manage where you buy toys from — eBay, local shops, conventions, and more.',
    'entityKey' => 'collection-source',
    'addBtnText' => 'Add Source'
]);
?>

<div class="modal fade" id="collection-source-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Purchase Source</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="collection-source-form">
                <div class="modal-body">
                    <?= $csrfField() ?>
                    <input type="hidden" name="id">

                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" class="form-control" name="name" required placeholder="e.g. eBay">
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Website</label>
                        <input type="url" class="form-control" name="website" placeholder="e.g. https://www.ebay.com">
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
    new EntityManager('collection-source', {
        mode: 'html',
        endpoint: '/collection-source',
        listUrl: '/collection-source/list',
    });
});
</script>

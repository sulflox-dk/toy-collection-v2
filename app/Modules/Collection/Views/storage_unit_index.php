<?php
echo $this->renderPartial('common/index_header', [
    'title' => 'Collection / Storage Units',
    'subtitle' => 'Manage physical boxes, shelves, or locations where your toys are stored.',
    'entityKey' => 'storage-unit',
    'addBtnText' => 'Add Storage Unit'
]);
?>

<div class="modal fade" id="storage-unit-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Storage Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="storage-unit-form">
                <div class="modal-body">
                    <?= $csrfField() ?>
                    <input type="hidden" name="id">
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Box ID / Code</label>
                            <input type="text" class="form-control" name="box_code" placeholder="e.g. SW-01">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Name *</label>
                            <input type="text" class="form-control" name="name" required placeholder="e.g. Star Wars Vehicles">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" placeholder="e.g. Attic, Shelf 3">
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="Brief description of what goes in this unit..."></textarea>
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
    new EntityManager('storage-unit', {
        mode: 'html',
        endpoint: '/storage-unit',
        listUrl: '/storage-unit/list',
    });
});
</script>
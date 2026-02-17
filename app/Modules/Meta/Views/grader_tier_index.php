<?php
echo $this->renderPartial('common/index_header', [
    'title' => 'Meta / Grader Tiers',
    'subtitle' => 'Grading levels (e.g. Gold, Silver, Archival).',
    'entityKey' => 'grader-tier',
    'addBtnText' => 'Add Tier',
    'showVisibility' => false
]);
?>

<div class="modal fade" id="grader-tier-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Grader Tier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="grader-tier-form">
                <div class="modal-body">
                    <?= $csrfField() ?>
                    <input type="hidden" name="id">
                    
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required placeholder="e.g. Gold">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control" name="slug" placeholder="Auto-generated if empty">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" class="form-control" name="sort_order" value="0">
                        <div class="form-text">Lower numbers appear first.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-link me-auto" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" data-action="save-close">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    new EntityManager('grader-tier', {
        mode: 'html',
        endpoint: '/grader-tier',
        listUrl: '/grader-tier/list',
    });
});
</script>
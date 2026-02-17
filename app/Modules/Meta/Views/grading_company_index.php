<?php
echo $this->renderPartial('common/index_header', [
    'title' => 'Meta / Grading Companies',
    'subtitle' => 'Professional grading services (e.g. AFA, UKG).',
    'entityKey' => 'grading-company',
    'addBtnText' => 'Add Company',
    'showVisibility' => false
]);
?>

<div class="modal fade" id="grading-company-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Grading Company</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="grading-company-form">
                <div class="modal-body">
                    <?= $csrfField() ?>
                    <input type="hidden" name="id">
                    
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required placeholder="e.g. Action Figure Authority">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Website</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted"><i class="fa-solid fa-link"></i></span>
                            <input type="url" class="form-control border-start-0 ps-0" name="website" placeholder="https://...">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control" name="slug" placeholder="Auto-generated if empty">
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
    new EntityManager('grading-company', {
        mode: 'html',
        endpoint: '/grading-company',
        listUrl: '/grading-company/list',
    });
});
</script>
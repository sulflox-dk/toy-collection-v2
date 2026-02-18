<?php
echo $this->renderPartial('common/index_header', [
    'title' => 'Meta / Condition Grades',
    'subtitle' => 'Standardized grading scale (e.g. C9, C8.5, Mint, Poor).',
    'entityKey' => 'condition-grade',
    'addBtnText' => 'Add Condition Grade',
    'showVisibility' => false
]);
?>

<div class="modal fade" id="condition-grade-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Condition Grade</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="condition-grade-form">
                <div class="modal-body">
                    <?= $csrfField() ?>
                    <input type="hidden" name="id">
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" required placeholder="e.g. Near Mint">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Abbreviation</label>
                            <input type="text" class="form-control" name="abbreviation" placeholder="e.g. C9">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="e.g. Minimal wear, tight joints..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control" name="slug" placeholder="Auto-generated if empty">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" class="form-control" name="sort_order" value="0">
                        <div class="form-text">Higher numbers usually mean better condition (or sort however you prefer).</div>
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
    new EntityManager('condition-grade', {
        mode: 'html',
        endpoint: '/condition-grade',
        listUrl: '/condition-grade/list',
    });
});
</script>
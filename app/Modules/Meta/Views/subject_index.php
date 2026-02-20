<?php
// 1. Build Custom Filters
ob_start(); 
?>
<div class="col-md-3 mb-2 mb-md-0">
    <select class="form-select data-filter" name="type">
        <option value="">All Types</option>
        <?php foreach ($types as $type): ?>
            <option value="<?= $type ?>"><?= $type ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div class="col-md-3 mb-2 mb-md-0">
    <select class="form-select data-filter" name="universe_id">
        <option value="">All Universes</option>
        <?php foreach ($universes as $u): ?>
            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<?php 
$customFilters = ob_get_clean();

// 2. Render Header (No visibility toggle needed)
echo $this->renderPartial('common/index_header', [
    'title' => 'Meta / Subjects',
    'subtitle' => 'Characters, Vehicles, Creatures, and more.',
    'entityKey' => 'subject',
    'addBtnText' => 'Add Subject',
    'extraFilters' => $customFilters,
    'showVisibility' => false
]);
?>

<div class="modal fade" id="subject-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="subject-form">
                <div class="modal-body">
                    <?= $csrfField() ?>
                    <input type="hidden" name="id">
                    
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required placeholder="e.g. Luke Skywalker">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select class="form-select" name="type">
                            <?php foreach ($types as $type): ?>
                                <option value="<?= $type ?>"><?= $type ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Universe</label>
                        <select class="form-select" name="universe_id" required>
                            <option value="">Select...</option>
                            <?php foreach ($universes as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="Optional details..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control" name="slug" placeholder="Auto-generated if empty">
                        <div class="invalid-feedback">Error message will appear here</div>
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
    new EntityManager('subject', {
        mode: 'html',
        endpoint: '/subject',
        listUrl: '/subject/list',
    });
});
</script>
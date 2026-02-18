<?php
// 1. Build Custom Filters (Role)
ob_start();
?>
<div class="col-md-2 mb-2 mb-md-0">
    <select class="form-select data-filter" name="role">
        <option value="">All Roles</option>
        <option value="admin">Admin</option>
        <option value="user">User</option>
    </select>
</div>
<?php
$customFilters = ob_get_clean();

// 2. Render Generic Header
echo $this->renderPartial('common/index_header', [
    'title' => 'User Management',
    'subtitle' => 'Manage user accounts and roles.',
    'entityKey' => 'user',
    'addBtnText' => 'Add User',
    'extraFilters' => $customFilters
]);
?>

<div class="modal fade" id="user-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="user-form">
                <div class="modal-body">
                    <?= $csrfField() ?>
                    <input type="hidden" name="id">

                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required placeholder="e.g. John Doe">
                        <div class="invalid-feedback">Error message will appear here</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required placeholder="e.g. john@example.com">
                        <div class="invalid-feedback">Error message will appear here</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" placeholder="Min 8 characters" autocomplete="new-password">
                        <div class="invalid-feedback">Error message will appear here</div>
                        <div class="form-text text-muted small password-hint">Leave empty to keep current password (when editing).</div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
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
    const mgr = new EntityManager('user', {
        mode: 'html',
        endpoint: '/user',
        listUrl: '/user/list',
    });

    // Override openModal to handle password field behavior
    const originalOpen = mgr.openModal.bind(mgr);
    mgr.openModal = function(data = null) {
        originalOpen(data);
        const pwField = document.querySelector('#user-form [name="password"]');
        const pwHint = document.querySelector('#user-form .password-hint');
        if (pwField) {
            if (data) {
                // Editing: password optional
                pwField.removeAttribute('required');
                pwField.placeholder = 'Leave empty to keep current';
                if (pwHint) pwHint.classList.remove('d-none');
            } else {
                // Creating: password required
                pwField.setAttribute('required', 'required');
                pwField.placeholder = 'Min 8 characters';
                if (pwHint) pwHint.classList.add('d-none');
            }
        }
    };
});
</script>

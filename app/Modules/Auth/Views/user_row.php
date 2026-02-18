<tr data-id="<?= $u['id'] ?>">
    <td class="ps-3">
        <div class="fw-bold"><?= $e($u['name']) ?></div>
        <small class="text-muted"><?= $e($u['email']) ?></small>
    </td>
    <td class="text-center">
        <?php if ($u['role'] === 'admin'): ?>
            <span class="badge bg-primary"><i class="fa-solid fa-shield-halved me-1"></i>Admin</span>
        <?php else: ?>
            <span class="badge bg-secondary">User</span>
        <?php endif; ?>
    </td>
    <td>
        <small class="text-muted"><?= $e($u['created_at'] ?? '') ?></small>
    </td>
    <td class="text-end pe-3">
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary btn-edit"
                    data-id="<?= $u['id'] ?>"
                    data-json='<?= json_encode($u, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>'>
                <i class="fa-solid fa-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary btn-delete" data-id="<?= $u['id'] ?>">
                <i class="fa-solid fa-trash-alt"></i>
            </button>
        </div>
    </td>
</tr>

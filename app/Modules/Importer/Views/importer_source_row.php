<tr data-id="<?= $e($s['id']) ?>">
    <td class="ps-3">
        <div class="fw-bold"><?= $e($s['name']) ?></div>
        <small class="text-muted"><?= $e($s['slug']) ?></small>
    </td>
    <td>
        <code class="small"><?= $e($s['base_url']) ?></code>
    </td>
    <td>
        <?php
        $driverShort = $s['driver_class'];
        if (($pos = strrpos($driverShort, '\\')) !== false) {
            $driverShort = substr($driverShort, $pos + 1);
        }
        ?>
        <span class="badge bg-light text-dark border"><?= $e($driverShort) ?></span>
    </td>
    <td class="text-center">
        <span class="badge bg-light text-dark border">
            <i class="fa-solid fa-file-import me-1"></i> <?= $s['item_count'] ?? 0 ?>
        </span>
    </td>
    <td class="text-center">
        <?php if ($s['is_active']): ?>
            <span class="badge bg-success">Active</span>
        <?php else: ?>
            <span class="badge bg-secondary">Inactive</span>
        <?php endif; ?>
    </td>
    <td class="text-end pe-3">
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary btn-edit"
                    data-id="<?= $e($s['id']) ?>"
                    data-json='<?= json_encode($s, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>'>
                <i class="fa-solid fa-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary btn-delete" data-id="<?= $e($s['id']) ?>">
                <i class="fa-solid fa-trash-alt"></i>
            </button>
        </div>
    </td>
</tr>

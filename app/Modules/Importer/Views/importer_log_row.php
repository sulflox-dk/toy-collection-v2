<tr>
    <td class="ps-3">
        <small class="text-muted"><?= $e($log['created_at'] ?? '') ?></small>
    </td>
    <td>
        <?= $e($log['source_name'] ?? '-') ?>
    </td>
    <td class="text-center">
        <?php
        $statusClass = match($log['status'] ?? '') {
            'Success' => 'bg-success',
            'Warning' => 'bg-warning text-dark',
            'Error'   => 'bg-danger',
            default   => 'bg-secondary'
        };
        ?>
        <span class="badge <?= $statusClass ?>"><?= $e($log['status'] ?? '-') ?></span>
    </td>
    <td>
        <span class="small"><?= $e($log['message'] ?? '') ?></span>
    </td>
    <td>
        <?php if (!empty($log['external_id'])): ?>
            <code class="small"><?= $e($log['external_id']) ?></code>
        <?php else: ?>
            <span class="text-muted">-</span>
        <?php endif; ?>
    </td>
</tr>

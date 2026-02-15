<tr data-id="<?= $e($s['id']) ?>">
    <td class="ps-3">
        <div class="fw-bold"><?= $e($s['name']) ?></div>
        <small class="text-muted"><?= $e($s['slug']) ?></small>
    </td>
    <td>
        <?= $e($s['type']) ?>
        <?php if(!empty($s['release_year'])): ?>
            / <?= $e($s['release_year']) ?>
        <?php endif; ?>
    </td>
    <td>
        <?= $e($s['universe_name'] ?? 'Unknown') ?>
    </td>
    <td class="text-center">
        <?php if($s['show_on_dashboard']): ?>
            Visible
        <?php else: ?>
            Hidden
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
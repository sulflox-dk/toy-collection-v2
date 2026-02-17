<tr data-id="<?= $e($company['id']) ?>">
    <td class="ps-3">
        <div class="fw-bold"><?= $e($company['name']) ?></div>
        <small class="text-muted"><?= $e($company['slug']) ?></small>
    </td>
    <td>
        <?php if (!empty($company['website'])): ?>
            <a href="<?= $e($company['website']) ?>" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                <i class="fa-solid fa-external-link-alt me-1 text-muted"></i> 
                <?= $e(parse_url($company['website'], PHP_URL_HOST) ?? 'Visit Website') ?>
            </a>
        <?php else: ?>
            <span class="text-muted">-</span>
        <?php endif; ?>
    </td>
    <td class="text-end pe-3">
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary btn-edit" 
                    data-id="<?= $e($company['id']) ?>" 
                    data-json='<?= json_encode($company, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>'>
                <i class="fa-solid fa-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary btn-delete" data-id="<?= $e($company['id']) ?>">
                <i class="fa-solid fa-trash-alt"></i>
            </button>
        </div>
    </td>
</tr>
<tr data-id="<?= $source['id'] ?>">
    <td class="ps-3">
        <div class="fw-bold"><?= $e($source['name']) ?></div>
    </td>
    <td>
        <?php if (!empty($source['website'])): ?>
            <a href="<?= $e($source['website']) ?>" target="_blank" rel="noopener noreferrer">
                <?= $e($source['website']) ?>
            </a>
        <?php else: ?>
            <span class="text-muted">&mdash;</span>
        <?php endif; ?>
    </td>
    <td class="text-end pe-3">
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary btn-edit"
                    data-id="<?= $source['id'] ?>"
                    data-json='<?= json_encode($source, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>'>
                <i class="fa-solid fa-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary btn-delete" data-id="<?= $source['id'] ?>">
                <i class="fa-solid fa-trash-alt"></i>
            </button>
        </div>
    </td>
</tr>

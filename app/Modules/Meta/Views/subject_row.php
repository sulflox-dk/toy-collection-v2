<?php $baseUrl = rtrim(\App\Kernel\Core\Config::get('app.url', ''), '/') . '/'; ?>
<tr data-id="<?= $e($s['id']) ?>">
    <td class="text-center">
        <?php if (!empty($s['image_path'])): ?>
            <img src="<?= $e($s['image_path']) ?>" class="rounded" style="width:40px;height:40px;object-fit:cover;">
        <?php else: ?>
            <div class="bg-light rounded d-inline-flex align-items-center justify-content-center text-muted" style="width:40px;height:40px;">
                <i class="fa-solid fa-image"></i>
            </div>
        <?php endif; ?>
    </td>
    <td class="ps-3">
        <div class="fw-bold">
            <?= $e($s['name']) ?>
            <?php if (!empty($s['external_url'])): ?>
                <a href="<?= $e($s['external_url']) ?>" target="_blank" rel="noopener" class="text-muted ms-1" title="<?= $e($s['external_url']) ?>">
                    <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.75em;"></i>
                </a>
            <?php endif; ?>
        </div>
        <small class="text-muted"><?= $e($s['slug']) ?></small>
    </td>
    <td>
        <?= $e($s['type']) ?>
    </td>
    <td>
        <?= $e($s['universe_name'] ?? 'Unknown') ?>
    </td>
    <td>
        <div class="text-muted text-truncate" style="max-width: 200px;">
            <?= $e($s['description'] ?? '') ?>
        </div>
    </td>
    <td class="text-end pe-3">
        <div class="btn-group">
            <a class="btn btn-sm btn-outline-secondary" title="View in Catalog Toys"
               href="<?= $e($baseUrl) ?>catalog-toy?subject_id=<?= (int) $s['id'] ?>" target="_blank" rel="noopener">
                <i class="fa-solid fa-cubes"></i>
            </a>
            <a class="btn btn-sm btn-outline-secondary" title="View in Collection"
               href="<?= $e($baseUrl) ?>collection-toy?subject_id=<?= (int) $s['id'] ?>" target="_blank" rel="noopener">
                <i class="fa-solid fa-box"></i>
            </a>
            <button class="btn btn-sm btn-outline-secondary" title="Manage Photo"
                    onclick='openEntityPhotoModal("subjects", <?= (int) $s['id'] ?>, <?= $e(json_encode($s['name'])) ?>)'>
                <i class="fa-solid fa-camera"></i>
            </button>
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

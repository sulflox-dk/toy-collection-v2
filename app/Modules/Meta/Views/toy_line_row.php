<?php $imgBaseUrl = rtrim(\App\Kernel\Core\Config::get('app.url', ''), '/') . '/'; ?>
<tr data-id="<?= $e($t['id']) ?>">
    <td class="text-center">
        <?php if (!empty($t['image_path'])): ?>
            <img src="<?= $imgBaseUrl . $e($t['image_path']) ?>" class="rounded" style="width:40px;height:40px;object-fit:cover;">
        <?php else: ?>
            <div class="bg-light rounded d-inline-flex align-items-center justify-content-center text-muted" style="width:40px;height:40px;">
                <i class="fa-solid fa-image"></i>
            </div>
        <?php endif; ?>
    </td>
    <td class="ps-3">
        <div class="fw-bold"><?= $e($t['name']) ?></div>
        <small class="text-muted"><?= $e($t['slug']) ?></small>
    </td>
    <td>
        <?= $e($t['manufacturer_name'] ?? 'Unknown') ?>
    </td>
    <td>
        <?= $e($t['universe_name'] ?? 'Unknown') ?>
    </td>
    <td class="text-center">
        <?php if($t['show_on_dashboard']): ?>
            Visible
        <?php else: ?>
            Hidden
        <?php endif; ?>
    </td>
    <td class="text-end pe-3">
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary btn-edit" 
                    data-id="<?= $e($t['id']) ?>" 
                    data-json='<?= json_encode($t, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>'>
                <i class="fa-solid fa-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary" title="Manage Photo"
                    onclick='openEntityPhotoModal("toy_lines", <?= (int) $t['id'] ?>, <?= $e(json_encode($t['name'])) ?>)'>
                <i class="fa-solid fa-camera"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary btn-delete" data-id="<?= $e($t['id']) ?>">
                <i class="fa-solid fa-trash-alt"></i>
            </button>
        </div>
    </td>
</tr>
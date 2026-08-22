<?php $imgBaseUrl = rtrim(\App\Kernel\Core\Config::get('app.url', ''), '/') . '/'; ?>
<tr data-id="<?= $e($s['id']) ?>">
    <td class="text-center">
        <?php if (!empty($s['image_path'])): ?>
            <img src="<?= $imgBaseUrl . $e($s['image_path']) ?>" class="rounded" style="width:40px;height:40px;object-fit:cover;">
        <?php else: ?>
            <div class="bg-light rounded d-inline-flex align-items-center justify-content-center text-muted" style="width:40px;height:40px;">
                <i class="fa-solid fa-image"></i>
            </div>
        <?php endif; ?>
    </td>
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
            <button class="btn btn-sm btn-outline-secondary" title="Manage Photo"
                    onclick='openEntityPhotoModal("entertainment_sources", <?= (int) $s['id'] ?>, <?= $e(json_encode($s['name'])) ?>)'>
                <i class="fa-solid fa-camera"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary btn-delete" data-id="<?= $e($s['id']) ?>">
                <i class="fa-solid fa-trash-alt"></i>
            </button>
        </div>
    </td>
</tr>
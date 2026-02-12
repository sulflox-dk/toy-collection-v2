<div class="table-responsive">
    <table class="table table-hover align-middle mb-0 border">
        <thead class="bg-light">
            <tr>
                <th class="ps-3" style="width: 40%;">Name</th>
                <th class="text-center">Dashboard</th>
                <th class="text-center">Toy Lines</th>
                <th class="text-end pe-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($manufacturers)): ?>
                <tr>
                    <td colspan="4" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-box-open fa-2x mb-2"></i>
                        <p class="mb-0">No manufacturers found.</p>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach($manufacturers as $m): ?>
                    <tr>
                        <td class="ps-3 fw-bold">
                            <?= $e($m['name']) ?>
                            <div class="small text-muted fw-normal">Slug: <?= $e($m['slug']) ?></div>
                        </td>
                        <td class="text-center">
                            <?php if($m['show_on_dashboard']): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="fa-solid fa-check me-1"></i> Visible
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                    Hidden
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border">
                                <i class="fa-solid fa-layer-group me-1"></i> <?= $m['lines_count'] ?? 0 ?>
                            </span>
                        </td>
                        <td class="text-end pe-3">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-secondary btn-edit" 
                                        data-id="<?= $m['id'] ?>"
                                        data-json='<?= json_encode($m, JSON_HEX_APOS) ?>'>
                                    <i class="fa-solid fa-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger btn-delete" 
                                        data-id="<?= $m['id'] ?>">
                                    <i class="fa-solid fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
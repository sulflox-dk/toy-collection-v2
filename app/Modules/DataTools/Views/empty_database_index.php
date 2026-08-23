<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Empty Database</h1>
        <p class="text-muted small mb-0">Wipe your collection data in three increasingly broad, cumulative levels.</p>
    </div>
</div>

<div class="alert alert-warning d-flex align-items-center justify-content-between mb-4">
    <div>
        <strong><i class="fa-solid fa-triangle-exclamation me-2"></i>Take a backup first.</strong>
        Every level below is permanent — there's no undo here, only a restore from a backup you took beforehand.
    </div>
    <a href="<?= $baseUrl ?>backup" class="btn btn-outline-dark btn-sm text-nowrap ms-3">
        <i class="fa-solid fa-download me-1"></i> Go to Backup
    </a>
</div>

<?php
$levelConfig = [
    1 => [
        'title' => 'Level 1 — Empty the Collection',
        'desc' => 'Everything about the toys you own: the collection itself, storage units, and purchase sources — plus their photos.',
    ],
    2 => [
        'title' => 'Level 2 — Empty the Collection + Catalog',
        'desc' => 'Everything from Level 1, plus the catalog itself (every toy and its included items) and its import history — plus their photos.',
    ],
    3 => [
        'title' => 'Level 3 — Empty Everything (Collection + Catalog + Meta Data)',
        'desc' => 'Everything from Levels 1 and 2, plus every reference table the catalog depends on: universes, manufacturers, toy lines, subjects, and the rest — plus their photos. This leaves the database essentially blank.',
    ],
];
?>

<?php foreach ($levelConfig as $level => $cfg): ?>
    <?php
        $tableCount = 0;
        $rowTotal = 0;
        for ($i = 1; $i <= $level; $i++) {
            foreach ($counts[$i] as $table => $count) {
                $tableCount++;
                $rowTotal += $count;
            }
        }
        $mediaTotal = 0;
        for ($i = 1; $i <= $level; $i++) {
            $mediaTotal += $mediaCounts[$i];
        }
    ?>
    <div class="card border-0 shadow-sm border-danger-subtle mb-4">
        <div class="card-header bg-white text-danger">
            <i class="fa-solid fa-trash-can me-2"></i>
            <strong><?= $e($cfg['title']) ?></strong>
        </div>
        <div class="card-body">
            <p class="text-muted"><?= $e($cfg['desc']) ?></p>

            <div class="mb-3">
                <button class="btn btn-link p-0 text-decoration-none small" type="button" data-bs-toggle="collapse" data-bs-target="#level-detail-<?= $level ?>">
                    <i class="fa-solid fa-list-ul me-1"></i> Show exactly what this includes (<?= $rowTotal ?> row<?= $rowTotal === 1 ? '' : 's' ?> across <?= $tableCount ?> table<?= $tableCount === 1 ? '' : 's' ?>, <?= $mediaTotal ?> photo<?= $mediaTotal === 1 ? '' : 's' ?>)
                </button>
                <div class="collapse mt-2" id="level-detail-<?= $level ?>">
                    <ul class="small text-muted mb-0">
                        <?php for ($i = 1; $i <= $level; $i++): ?>
                            <?php foreach ($counts[$i] as $table => $count): ?>
                                <li><?= $e($tableLabels[$table] ?? $table) ?>: <strong><?= $count ?></strong> row<?= $count === 1 ? '' : 's' ?></li>
                            <?php endforeach; ?>
                            <li>Photos attached to <?= $e($levelLabels[$i]) ?> records: <strong><?= $mediaCounts[$i] ?></strong></li>
                        <?php endfor; ?>
                    </ul>
                </div>
            </div>

            <form class="empty-level-form" data-level="<?= $level ?>">
                <?= $csrfField() ?>
                <div class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small text-muted mb-1">Type <code>EMPTY</code> to confirm</label>
                        <input type="text" class="form-control confirm-input" autocomplete="off" placeholder="EMPTY">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-danger btn-empty-level" disabled>
                            <i class="fa-solid fa-trash-can me-2"></i> Empty Level <?= $level ?>
                        </button>
                    </div>
                </div>
                <div class="small mt-2 empty-status"></div>
            </form>
        </div>
    </div>
<?php endforeach; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.empty-level-form').forEach((form) => {
        const input = form.querySelector('.confirm-input');
        const btn = form.querySelector('.btn-empty-level');
        const status = form.querySelector('.empty-status');
        const level = form.dataset.level;

        input.addEventListener('input', () => {
            btn.disabled = input.value !== 'EMPTY';
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (input.value !== 'EMPTY') return;

            if (!confirm(`Really empty Level ${level}? This permanently deletes the data listed above and cannot be undone.`)) {
                return;
            }

            btn.disabled = true;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Emptying...';
            status.textContent = '';
            status.className = 'small mt-2 empty-status';

            try {
                const result = await ApiClient.post('<?= $baseUrl ?>empty-database/level/' + level, { confirm: 'EMPTY' });
                if (result.success) {
                    status.textContent = `Done — ${result.tablesEmptied} table(s) emptied, ${result.filesDeleted} photo(s) deleted. Reloading...`;
                    status.className = 'small mt-2 text-success';
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    status.textContent = result.error || 'Failed.';
                    status.className = 'small mt-2 text-danger';
                    btn.innerHTML = originalHtml;
                    btn.disabled = input.value !== 'EMPTY';
                }
            } catch (error) {
                status.textContent = error.message || 'Failed.';
                status.className = 'small mt-2 text-danger';
                btn.innerHTML = originalHtml;
                btn.disabled = input.value !== 'EMPTY';
            }
        });
    });
});
</script>

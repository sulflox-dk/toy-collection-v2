<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Backup &amp; Restore</h1>
        <p class="text-muted small mb-0">Download a full snapshot of your collection, or restore from one.</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold">Database</div>
                <div class="h4 mb-0 mt-1"><?= (int) $tableCount ?> tables</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold">Media Library</div>
                <div class="h4 mb-0 mt-1"><?= (int) $mediaFileCount ?> files <span class="text-muted small">(<?= $e($mediaSizeFormatted) ?>)</span></div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <i class="fa-solid fa-download me-2 text-primary"></i>
        <strong>Download Backup</strong>
    </div>
    <div class="card-body">
        <p class="text-muted">
            Bundles a full database dump and every file in your media library into a single <code>.zip</code>.
            Safe to run any time — this only reads your data, nothing is changed.
        </p>
        <a href="<?= $baseUrl ?>backup/download" class="btn btn-primary">
            <i class="fa-solid fa-download me-2"></i> Download Backup (.zip)
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm border-danger-subtle mb-4">
    <div class="card-header bg-white text-danger">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>
        <strong>Restore from Backup</strong>
    </div>
    <div class="card-body">
        <div class="alert alert-danger">
            <strong>This replaces everything.</strong> Restoring wipes your current database and media library and
            reloads them from the uploaded file — it's a full swap, not a merge. Anything added since that backup
            was taken will be lost. If a restore fails partway through, your database may be left in a mixed state —
            restore again from a known-good backup to recover.
        </div>

        <div class="form-text mb-3">
            This server currently allows uploads up to <strong><?= $e($uploadMaxFilesize) ?></strong>
            (<code>upload_max_filesize</code>) and requests up to <strong><?= $e($postMaxSize) ?></strong>
            (<code>post_max_size</code>). If your backup file is larger than that, raise both in <code>php.ini</code>
            and restart the server before restoring.
        </div>

        <form id="restoreForm">
            <?= $csrfField() ?>
            <div class="mb-3">
                <label class="form-label">Backup file</label>
                <input type="file" class="form-control" id="restoreFile" name="backup_file" accept=".zip" required>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="restoreConfirm">
                <label class="form-check-label" for="restoreConfirm">
                    I understand this will permanently replace my current database and media library.
                </label>
            </div>
            <button type="submit" class="btn btn-danger" id="btnRestore" disabled>
                <i class="fa-solid fa-triangle-exclamation me-2"></i> Restore from Backup
            </button>
            <div id="restoreStatus" class="small mt-2"></div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('restoreForm');
    const confirmBox = document.getElementById('restoreConfirm');
    const fileInput = document.getElementById('restoreFile');
    const btnRestore = document.getElementById('btnRestore');
    const statusEl = document.getElementById('restoreStatus');

    function updateButtonState() {
        btnRestore.disabled = !(confirmBox.checked && fileInput.files.length > 0);
    }
    confirmBox.addEventListener('change', updateButtonState);
    fileInput.addEventListener('change', updateButtonState);

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!confirmBox.checked || fileInput.files.length === 0) return;

        if (!confirm('Really restore? This permanently replaces your current database and media library.')) {
            return;
        }

        const formData = new FormData();
        formData.append('backup_file', fileInput.files[0]);
        formData.append('confirm', 'yes');

        btnRestore.disabled = true;
        const originalHtml = btnRestore.innerHTML;
        btnRestore.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Restoring...';
        statusEl.textContent = '';
        statusEl.className = 'small mt-2';

        try {
            const result = await ApiClient.post('<?= $baseUrl ?>backup/restore', formData);
            if (result.success) {
                statusEl.textContent = `Restore complete — ${result.manifest.tables} table(s), ${result.manifest.media_files} media file(s). Reloading...`;
                statusEl.className = 'small mt-2 text-success';
                setTimeout(() => window.location.reload(), 1500);
            } else {
                statusEl.textContent = result.error || 'Restore failed.';
                statusEl.className = 'small mt-2 text-danger';
                btnRestore.disabled = false;
                btnRestore.innerHTML = originalHtml;
            }
        } catch (error) {
            statusEl.textContent = error.message || 'Restore failed.';
            statusEl.className = 'small mt-2 text-danger';
            btnRestore.disabled = false;
            btnRestore.innerHTML = originalHtml;
        }
    });
});
</script>

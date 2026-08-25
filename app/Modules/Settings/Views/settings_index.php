<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Settings</h1>
        <p class="text-muted small mb-0">Cross-system preferences that apply everywhere, not just to one entry.</p>
    </div>
</div>

<?php if ($saved): ?>
    <div class="alert alert-success border-0 shadow-sm">
        <i class="fa-solid fa-check-circle me-2"></i> Settings saved.
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-4" style="max-width: 500px;">
    <div class="card-header bg-white">
        <i class="fa-solid fa-coins me-2 text-primary"></i>
        <strong>Currency</strong>
    </div>
    <div class="card-body">
        <p class="text-muted">
            Every purchase price and current value in your collection is entered in this currency.
            Keeping it consistent makes it possible to add up money invested across universes,
            manufacturers, and toy lines without a conversion step.
        </p>
        <form method="post" action="<?= $baseUrl ?>settings">
            <?= $csrfField() ?>
            <input type="hidden" name="_method" value="PUT">
            <label class="form-label small text-muted mb-1">Currency</label>
            <div class="d-flex gap-2">
                <select class="form-select" name="currency" style="max-width: 200px;">
                    <?php foreach ($currencies as $cur): ?>
                        <option value="<?= $cur ?>" <?= $settings['currency'] === $cur ? 'selected' : '' ?>><?= $cur ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-dark">Save</button>
            </div>
        </form>
    </div>
</div>

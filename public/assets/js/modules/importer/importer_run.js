document.addEventListener('DOMContentLoaded', () => {
	const btnPreview = document.getElementById('btnPreview');
	const importUrl = document.getElementById('importUrl');
	const resultsGrid = document.getElementById('resultsGrid');
	const importResults = document.getElementById('importResults');
	const btnRunImport = document.getElementById('btnRunImport');
	const btnSelectAll = document.getElementById('btnSelectAll');
	const btnDeselectAll = document.getElementById('btnDeselectAll');

	let currentItems = [];

	// Allow Enter key in URL input
	importUrl.addEventListener('keydown', (e) => {
		if (e.key === 'Enter') {
			e.preventDefault();
			btnPreview.click();
		}
	});

	// Preview / Analyze
	btnPreview.addEventListener('click', async () => {
		const url = importUrl.value.trim();
		if (!url) return;

		importResults.classList.remove('d-none');
		UiHelper.showLoader('#resultsGrid');
		btnPreview.disabled = true;

		try {
			const formData = new FormData();
			formData.append('url', url);

			const result = await ApiClient.post(
				SITE_URL + 'importer-run/preview',
				formData,
			);

			if (result.success) {
				currentItems = result.data;
				document.getElementById('itemCount').textContent =
					currentItems.length;
				document.getElementById('sourceName').textContent =
					'Source: ' + result.source;

				if (currentItems.length === 0) {
					resultsGrid.innerHTML =
						'<div class="alert alert-warning"><i class="fa-solid fa-triangle-exclamation me-2"></i>No items found on this URL.</div>';
				} else {
					renderGrid(currentItems);
				}
			} else {
				resultsGrid.innerHTML = `<div class="alert alert-danger"><i class="fa-solid fa-circle-xmark me-2"></i>${UiHelper.escapeHtml(result.error || 'Unknown error')}</div>`;
			}
		} catch (error) {
			const msg =
				error instanceof ApiError ? error.message : error.message;
			resultsGrid.innerHTML = `<div class="alert alert-danger"><i class="fa-solid fa-circle-xmark me-2"></i>${UiHelper.escapeHtml(msg)}</div>`;
		} finally {
			btnPreview.disabled = false;
		}
	});

	// Render results grid
	function renderGrid(items) {
		resultsGrid.innerHTML = '';

		items.forEach((item, index) => {
			let cardClass = 'border-success';
			let badge = '<span class="badge bg-success">NEW</span>';
			let statusHtml =
				'<span class="text-success"><i class="fa-solid fa-check-circle"></i> Ready to Create</span>';

			if (item.status === 'conflict') {
				cardClass = 'border-warning';
				badge =
					'<span class="badge bg-warning text-dark">CONFLICT</span>';
				statusHtml = `<span class="text-warning"><i class="fa-solid fa-triangle-exclamation"></i> ${esc(item.matchReason)} (ID: ${item.existingId})</span>`;
			} else if (item.status === 'linked') {
				cardClass = 'border-info';
				badge = '<span class="badge bg-info">LINKED</span>';
				statusHtml = `<span class="text-info"><i class="fa-solid fa-link"></i> Will update ID: ${item.existingId}</span>`;
			}

			// Image
			let imgHtml = '';
			if (item.images && item.images.length > 0) {
				imgHtml = `<img src="${esc(item.images[0])}" class="img-fluid rounded-start h-100" style="object-fit: contain; max-height: 280px; width: 100%; background: #f8f9fa;">`;
			} else {
				imgHtml = `<div class="d-flex align-items-center justify-content-center bg-light h-100" style="min-height: 180px;">
                    <span class="text-muted"><i class="fa-solid fa-image fa-2x"></i></span>
                </div>`;
			}

			// Accessories
			let itemsHtml =
				'<span class="text-muted fst-italic">None detected</span>';
			if (item.items && item.items.length > 0) {
				itemsHtml = item.items
					.map(
						(i) =>
							`<span class="badge bg-light text-dark border me-1 mb-1">${esc(i)}</span>`,
					)
					.join('');
			}

			const col = document.createElement('div');
			col.className = 'col-12 mb-3';
			col.innerHTML = `
                <div class="card ${cardClass} shadow-sm">
                    <div class="row g-0">
                        <div class="col-md-2 border-end">
                            ${imgHtml}
                        </div>
                        <div class="col-md-10">
                            <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-2">
                                <div>
                                    ${badge}
                                    <code class="ms-2 small text-muted">${esc(item.externalId)}</code>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input item-select" type="checkbox" value="${index}" checked>
                                    <label class="form-check-label fw-bold small">Include</label>
                                </div>
                            </div>
                            <div class="card-body py-2">
                                <h5 class="card-title text-primary mb-2">${esc(item.name)}</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-sm table-borderless mb-0">
                                            <tbody>
                                                <tr><td class="text-muted" style="width:100px">Year</td><td><strong>${esc(item.year || '-')}</strong></td></tr>
                                                <tr><td class="text-muted">Toy Line</td><td>${esc(item.toyLine || '-')}</td></tr>
                                                <tr><td class="text-muted">Manufacturer</td><td>${esc(item.manufacturer || '-')}</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-sm table-borderless mb-0">
                                            <tbody>
                                                <tr><td class="text-muted" style="width:100px">Wave</td><td>${esc(item.wave || '-')}</td></tr>
                                                <tr><td class="text-muted">SKU</td><td>${esc(item.assortmentSku || '-')}</td></tr>
                                                <tr><td class="text-muted">Status</td><td>${statusHtml}</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                ${
									item.items && item.items.length > 0
										? `<div class="mt-2 pt-2 border-top">
                                    <small class="text-uppercase text-muted fw-bold">Accessories</small>
                                    <div class="mt-1">${itemsHtml}</div>
                                </div>`
										: ''
								}
                            </div>
                        </div>
                    </div>
                </div>
            `;
			resultsGrid.appendChild(col);
		});
	}

	// Select / Deselect all
	btnSelectAll.addEventListener('click', () => {
		document
			.querySelectorAll('.item-select')
			.forEach((cb) => (cb.checked = true));
	});

	btnDeselectAll.addEventListener('click', () => {
		document
			.querySelectorAll('.item-select')
			.forEach((cb) => (cb.checked = false));
	});

	// Run Import
	btnRunImport.addEventListener('click', async () => {
		const checkboxes = document.querySelectorAll('.item-select:checked');
		const selectedIndices = Array.from(checkboxes).map((cb) =>
			parseInt(cb.value),
		);
		const itemsToImport = selectedIndices.map((i) => currentItems[i]);

		if (itemsToImport.length === 0) {
			UiHelper.showError('No items selected');
			return;
		}

		if (
			!confirm(
				`Import ${itemsToImport.length} item(s) into the catalog?`,
			)
		)
			return;

		btnRunImport.disabled = true;
		const originalHtml = btnRunImport.innerHTML;
		btnRunImport.innerHTML =
			'<i class="fa-solid fa-spinner fa-spin me-2"></i> Importing...';

		try {
			const csrfMeta = document.querySelector('meta[name="csrf-token"]');
			const result = await ApiClient.request(
				SITE_URL + 'importer-run/import',
				{
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-CSRF-Token': csrfMeta ? csrfMeta.content : '',
					},
					body: JSON.stringify({ items: itemsToImport }),
				},
			);

			if (result.success) {
				UiHelper.showSuccess(
					`Successfully imported ${result.count} item(s)!`,
				);
				if (result.errors && result.errors.length > 0) {
					result.errors.forEach((err) => UiHelper.showError(err));
				}
				importResults.classList.add('d-none');
				importUrl.value = '';
				currentItems = [];
			} else {
				UiHelper.showError(result.error || 'Import failed');
			}
		} catch (error) {
			const msg =
				error instanceof ApiError ? error.message : error.message;
			UiHelper.showError(msg);
		} finally {
			btnRunImport.disabled = false;
			btnRunImport.innerHTML = originalHtml;
		}
	});

	// Escape HTML helper
	function esc(str) {
		if (!str) return '';
		const div = document.createElement('div');
		div.textContent = String(str);
		return div.innerHTML;
	}
});

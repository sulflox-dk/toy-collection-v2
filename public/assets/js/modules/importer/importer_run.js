document.addEventListener('DOMContentLoaded', () => {
	const btnPreview = document.getElementById('btnPreview');
	const importUrl = document.getElementById('importUrl');
	const importOffset = document.getElementById('importOffset');
	const offsetInfo = document.getElementById('offsetInfo');
	const batchUniverse = document.getElementById('batchUniverse');
	const batchManufacturer = document.getElementById('batchManufacturer');
	const batchToyLine = document.getElementById('batchToyLine');
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
			formData.append('offset', importOffset.value || '0');
			if (batchUniverse.value) formData.append('universe_id', batchUniverse.value);
			if (batchManufacturer.value) formData.append('manufacturer_id', batchManufacturer.value);
			if (batchToyLine.value) formData.append('toy_line_id', batchToyLine.value);

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

				if (result.totalFound !== null && result.totalFound !== undefined) {
					const start = result.offset + 1;
					const end = result.offset + currentItems.length;
					offsetInfo.textContent = `Showing ${start}-${end} of ${result.totalFound} found`;
				} else {
					offsetInfo.textContent = '';
				}

				if (currentItems.length === 0) {
					resultsGrid.innerHTML =
						'<div class="alert alert-warning"><i class="fa-solid fa-triangle-exclamation me-2"></i>No items found on this URL or offset.</div>';
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

	function selectOptionsHtml(list, selectedId, placeholder) {
		let html = `<option value="">${esc(placeholder)}</option>`;
		list.forEach((opt) => {
			const sel = selectedId && Number(selectedId) === opt.id ? 'selected' : '';
			html += `<option value="${opt.id}" ${sel}>${esc(opt.name)}</option>`;
		});
		return html;
	}

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

			// Accessories (read-only — not editable here)
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

			const isExisting = item.status === 'conflict' || item.status === 'linked';

			const col = document.createElement('div');
			col.className = 'col-12 mb-3';
			col.dataset.index = index;
			col.innerHTML = `
                <div class="card ${cardClass} shadow-sm import-row" data-index="${index}">
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
                                <input type="text" class="form-control form-control-sm fw-bold text-primary mb-2 field-name" value="${esc(item.name)}">
                                ${isExisting ? `<div class="small mb-2">${statusHtml} — fields below are ignored for existing items, only the link is created.</div>` : ''}
                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <label class="form-label small text-muted mb-0">Year</label>
                                        <input type="text" class="form-control form-control-sm field-year" value="${esc(item.year || '')}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted mb-0">Wave</label>
                                        <input type="text" class="form-control form-control-sm field-wave" value="${esc(item.wave || '')}">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label small text-muted mb-0">SKU</label>
                                        <input type="text" class="form-control form-control-sm field-sku" value="${esc(item.assortmentSku || '')}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted mb-0">Universe</label>
                                        <select class="form-select form-select-sm field-universe">
                                            ${selectOptionsHtml(IMPORTER_LOOKUPS.universes, item.universe_id, '-- Select --')}
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted mb-0">Manufacturer</label>
                                        <select class="form-select form-select-sm field-manufacturer">
                                            ${selectOptionsHtml(IMPORTER_LOOKUPS.manufacturers, item.manufacturer_id, item.manufacturer || '-- Select --')}
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted mb-0">Toy Line <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm field-toy-line">
                                            ${selectOptionsHtml(IMPORTER_LOOKUPS.toyLines, item.toy_line_id, item.toyLine || '-- Select --')}
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted mb-0">Product Type</label>
                                        <select class="form-select form-select-sm field-product-type">
                                            ${selectOptionsHtml(IMPORTER_LOOKUPS.productTypes, item.product_type_id, '-- Select --')}
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted mb-0">Entertainment Source</label>
                                        <select class="form-select form-select-sm field-entertainment-source">
                                            ${selectOptionsHtml(IMPORTER_LOOKUPS.entertainmentSources, item.entertainment_source_id, '-- Select --')}
                                        </select>
                                    </div>
                                </div>
                                ${
									item.items && item.items.length > 0
										? `<div class="mt-2 pt-2 border-top">
                                    <small class="text-uppercase text-muted fw-bold">Accessories (detected, not editable here)</small>
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

	// Read the live (possibly user-edited) values for one row out of the DOM,
	// merged onto the original scraped item (which still carries source_id,
	// externalId, externalUrl, status, existingId, and the accessories list).
	function readRow(index) {
		const original = currentItems[index];
		const row = resultsGrid.querySelector(`.import-row[data-index="${index}"]`);
		if (!row) return original;

		const val = (sel) => row.querySelector(sel)?.value ?? '';

		return {
			...original,
			name: val('.field-name'),
			year: val('.field-year'),
			wave: val('.field-wave'),
			assortmentSku: val('.field-sku'),
			universe_id: val('.field-universe') || null,
			manufacturer_id: val('.field-manufacturer') || null,
			toy_line_id: val('.field-toy-line') || null,
			product_type_id: val('.field-product-type') || null,
			entertainment_source_id: val('.field-entertainment-source') || null,
		};
	}

	// Run Import
	btnRunImport.addEventListener('click', async () => {
		const checkboxes = document.querySelectorAll('.item-select:checked');
		const selectedIndices = Array.from(checkboxes).map((cb) =>
			parseInt(cb.value),
		);
		const itemsToImport = selectedIndices.map((i) => readRow(i));

		if (itemsToImport.length === 0) {
			UiHelper.showError('No items selected');
			return;
		}

		// catalog_toys.toy_line_id is required — catch this before submitting
		// rather than letting each one fail individually server-side.
		const missingToyLine = itemsToImport.filter(
			(item) => item.status === 'new' && !item.toy_line_id,
		);
		if (missingToyLine.length > 0) {
			UiHelper.showError(
				`${missingToyLine.length} selected item(s) have no Toy Line set — pick one (per item, or via the batch Toy Line field above and re-analyze) before importing: ${missingToyLine.map((i) => i.name).join(', ')}`,
			);
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

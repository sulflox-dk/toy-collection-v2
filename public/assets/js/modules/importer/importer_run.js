document.addEventListener('DOMContentLoaded', () => {
	const importUrl = document.getElementById('importUrl');
	const importOffset = document.getElementById('importOffset');
	const btnAddSource = document.getElementById('btnAddSource');
	const batchUniverse = document.getElementById('batchUniverse');
	const batchManufacturer = document.getElementById('batchManufacturer');
	const batchToyLine = document.getElementById('batchToyLine');
	const queueEl = document.getElementById('importQueue');
	const queueEmpty = document.getElementById('importQueueEmpty');
	const btnRunImport = document.getElementById('btnRunImport');
	const btnSelectAll = document.getElementById('btnSelectAll');
	const btnDeselectAll = document.getElementById('btnDeselectAll');
	const addSourceStatus = document.getElementById('addSourceStatus');

	const baseUrl = typeof SITE_URL !== 'undefined' ? SITE_URL : '/';
	const MAX_SOURCES_PER_GROUP = typeof IMPORTER_MAX_SOURCES_PER_GROUP !== 'undefined' ? IMPORTER_MAX_SOURCES_PER_GROUP : 6;

	let groups = [];
	let groupIdSeq = 0;

	// ---------------------------------------------------------------
	// Server calls
	// ---------------------------------------------------------------

	async function analyzeUrl(url, offset) {
		const formData = new FormData();
		formData.append('url', url);
		formData.append('offset', offset || 0);
		if (batchUniverse.value) formData.append('universe_id', batchUniverse.value);
		if (batchManufacturer.value) formData.append('manufacturer_id', batchManufacturer.value);
		if (batchToyLine.value) formData.append('toy_line_id', batchToyLine.value);
		return ApiClient.post(baseUrl + 'importer-run/analyze-url', formData);
	}

	async function searchCatalog(q) {
		return ApiClient.get(baseUrl + 'importer-run/search-catalog', { q });
	}

	async function fetchCatalogToy(id) {
		return ApiClient.get(baseUrl + 'importer-run/catalog-toy/' + id);
	}

	// ---------------------------------------------------------------
	// Group model helpers
	// ---------------------------------------------------------------

	function newGroup(firstResult) {
		const g = {
			id: ++groupIdSeq,
			urlResults: [firstResult],
			included: true,
			target: { mode: 'create', catalogToyId: null, toyLabel: '' },
			currentToyData: null,
			searchOpen: false,
			searchResults: [],
		};
		recomputeMerge(g);
		autoDetectTarget(g);
		return g;
	}

	// Recompute the merged single-value fields (agree vs conflict) and the
	// pooled list fields (images, accessories) from this group's raw
	// per-URL scrape results.
	function recomputeMerge(g) {
		const fieldDefs = [
			{ key: 'name', label: 'Name' },
			{ key: 'year', label: 'Year' },
			{ key: 'wave', label: 'Wave' },
			{ key: 'assortmentSku', label: 'SKU' },
			{ key: 'manufacturer_id', label: 'Manufacturer', lookup: 'manufacturers' },
			{ key: 'toy_line_id', label: 'Toy Line', lookup: 'toyLines' },
		];

		const merged = {};
		const conflicts = {};

		fieldDefs.forEach((def) => {
			const valuesBySupport = new Map(); // value -> [source names]
			g.urlResults.forEach((r) => {
				const v = r[def.key];
				if (v === null || v === undefined || v === '') return;
				const key = String(v);
				if (!valuesBySupport.has(key)) valuesBySupport.set(key, []);
				valuesBySupport.get(key).push(r.source_name);
			});

			const distinct = Array.from(valuesBySupport.keys());
			if (distinct.length === 0) {
				merged[def.key] = '';
			} else if (distinct.length === 1) {
				merged[def.key] = distinct[0];
			} else {
				// Conflict — default to whichever value has the most
				// supporting sources; ties keep the first one found.
				let best = distinct[0];
				distinct.forEach((v) => {
					if (valuesBySupport.get(v).length > valuesBySupport.get(best).length) best = v;
				});
				merged[def.key] = best;
				conflicts[def.key] = distinct.map((v) => ({
					value: v,
					sources: valuesBySupport.get(v),
				}));
			}
		});

		// Universe/product type/entertainment source aren't per-URL
		// conflict candidates (universe comes from the batch default;
		// product type/entertainment source are never scraped) — just
		// carry through the first non-empty value found, editable as normal.
		merged.universe_id = g.urlResults.find((r) => r.universe_id)?.universe_id || '';
		merged.product_type_id = '';
		merged.entertainment_source_id = '';

		// Free-text prose isn't worth a conflict picker if two sources
		// disagree (they virtually always will) — just take the first
		// source's description and let it be edited directly.
		merged.description = g.urlResults.find((r) => r.description)?.description || '';

		// Pooled list fields
		const accessories = [];
		const seenAcc = new Set();
		g.urlResults.forEach((r) => {
			(r.items || []).forEach((name) => {
				const key = name.trim().toLowerCase();
				if (key && !seenAcc.has(key)) {
					seenAcc.add(key);
					accessories.push(name.trim());
				}
			});
		});

		const images = [];
		const seenImg = new Set();
		g.urlResults.forEach((r) => {
			(r.images || []).forEach((url) => {
				if (!seenImg.has(url)) {
					seenImg.add(url);
					images.push(url);
				}
			});
		});

		g.merged = merged;
		g.conflicts = conflicts;
		g.accessories = accessories;
		g.images = images;
	}

	// If any contributing URL matched an existing toy, suggest that as the
	// target — still overridable via the manual search.
	function autoDetectTarget(g) {
		const match = g.urlResults.find((r) => r.status === 'linked' || r.status === 'conflict');
		if (match && match.existingId) {
			setGroupTarget(g, 'update', match.existingId, match.name + ` (${match.matchReason})`);
		}
	}

	function setGroupTarget(g, mode, catalogToyId, label) {
		g.target = { mode, catalogToyId, toyLabel: label || '' };
		g.currentToyData = null;
		if (mode === 'update' && catalogToyId) {
			fetchCatalogToy(catalogToyId).then((res) => {
				if (res.success) {
					g.currentToyData = res;
					renderAll();
				}
			}).catch(() => {});
		}
	}

	// ---------------------------------------------------------------
	// Top "Add Sources" box — creates one NEW group per scraped result.
	// A single detail page = one new group. A listing page = many new
	// groups at once (this is what makes bulk import fast).
	// ---------------------------------------------------------------

	btnAddSource.addEventListener('click', async () => {
		const url = importUrl.value.trim();
		if (!url) return;

		btnAddSource.disabled = true;
		addSourceStatus.textContent = 'Analyzing...';
		addSourceStatus.className = 'text-muted small ms-2';

		try {
			const result = await analyzeUrl(url, importOffset.value || 0);
			if (!result.success) {
				addSourceStatus.textContent = result.error || 'Failed to analyze URL';
				addSourceStatus.className = 'text-danger small ms-2';
				return;
			}

			result.data.forEach((item) => {
				groups.push(newGroup(item));
			});

			if (result.totalFound !== null && result.totalFound !== undefined) {
				const start = result.offset + 1;
				const end = result.offset + result.data.length;
				addSourceStatus.textContent = `Added ${result.data.length} (showing ${start}-${end} of ${result.totalFound} found on this page)`;
			} else {
				addSourceStatus.textContent = `Added 1 item from ${result.source}`;
			}
			addSourceStatus.className = 'text-success small ms-2';

			importUrl.value = '';
			renderAll();
		} catch (error) {
			addSourceStatus.textContent = error.message || 'Failed to analyze URL';
			addSourceStatus.className = 'text-danger small ms-2';
		} finally {
			btnAddSource.disabled = false;
		}
	});

	importUrl.addEventListener('keydown', (e) => {
		if (e.key === 'Enter') {
			e.preventDefault();
			btnAddSource.click();
		}
	});

	// ---------------------------------------------------------------
	// Rendering
	// ---------------------------------------------------------------

	function selectOptionsHtml(list, selectedId, placeholder) {
		let html = `<option value="">${esc(placeholder)}</option>`;
		list.forEach((opt) => {
			const sel = selectedId && String(selectedId) === String(opt.id) ? 'selected' : '';
			html += `<option value="${opt.id}" ${sel}>${esc(opt.name)}</option>`;
		});
		return html;
	}

	function conflictBadge(g, fieldKey) {
		if (!g.conflicts[fieldKey]) return '';
		const opts = g.conflicts[fieldKey]
			.map((c) => `<option value="${esc(c.value)}">${esc(c.value)} (${esc(c.sources.join(', '))})</option>`)
			.join('');
		return `<select class="form-select form-select-sm mt-1 conflict-pick" data-field="${fieldKey}">${opts}</select>
			<div class="small text-warning mt-1"><i class="fa-solid fa-code-compare me-1"></i>Sources disagree — pick one</div>`;
	}

	function renderCreateFields(g) {
		return `
			<input type="text" class="form-control form-control-sm fw-bold text-primary mb-2 field-name" value="${esc(g.merged.name)}">
			<div class="row g-2">
				<div class="col-md-3">
					<label class="form-label small text-muted mb-0">Year</label>
					<input type="text" class="form-control form-control-sm field-year" value="${esc(g.merged.year)}">
					${conflictBadge(g, 'year')}
				</div>
				<div class="col-md-4">
					<label class="form-label small text-muted mb-0">Wave</label>
					<input type="text" class="form-control form-control-sm field-wave" value="${esc(g.merged.wave)}">
					${conflictBadge(g, 'wave')}
				</div>
				<div class="col-md-5">
					<label class="form-label small text-muted mb-0">SKU</label>
					<input type="text" class="form-control form-control-sm field-sku" value="${esc(g.merged.assortmentSku)}">
					${conflictBadge(g, 'assortmentSku')}
				</div>
				<div class="col-md-4">
					<label class="form-label small text-muted mb-0">Universe</label>
					<select class="form-select form-select-sm field-universe">${selectOptionsHtml(IMPORTER_LOOKUPS.universes, g.merged.universe_id, '-- Select --')}</select>
				</div>
				<div class="col-md-4">
					<label class="form-label small text-muted mb-0">Manufacturer</label>
					<select class="form-select form-select-sm field-manufacturer">${selectOptionsHtml(IMPORTER_LOOKUPS.manufacturers, g.merged.manufacturer_id, '-- Select --')}</select>
					${conflictBadge(g, 'manufacturer_id')}
				</div>
				<div class="col-md-4">
					<label class="form-label small text-muted mb-0">Toy Line <span class="text-danger">*</span></label>
					<select class="form-select form-select-sm field-toy-line">${selectOptionsHtml(IMPORTER_LOOKUPS.toyLines, g.merged.toy_line_id, '-- Select --')}</select>
					${conflictBadge(g, 'toy_line_id')}
				</div>
				<div class="col-md-6">
					<label class="form-label small text-muted mb-0">Product Type</label>
					<select class="form-select form-select-sm field-product-type">${selectOptionsHtml(IMPORTER_LOOKUPS.productTypes, g.merged.product_type_id, '-- Select --')}</select>
				</div>
				<div class="col-md-6">
					<label class="form-label small text-muted mb-0">Entertainment Source</label>
					<select class="form-select form-select-sm field-entertainment-source">${selectOptionsHtml(IMPORTER_LOOKUPS.entertainmentSources, g.merged.entertainment_source_id, '-- Select --')}</select>
				</div>
				<div class="col-md-12">
					<label class="form-label small text-muted mb-0">Description</label>
					<textarea class="form-control form-control-sm field-description" rows="2">${esc(g.merged.description)}</textarea>
				</div>
			</div>
		`;
	}

	function compareRow(fieldKey, label, currentVal, foundVal, hasConflict, g) {
		const currentDisplay = currentVal ? esc(currentVal) : '<span class="text-muted fst-italic">— empty —</span>';
		const foundDisplay = hasConflict
			? conflictBadge(g, fieldKey)
			: (foundVal ? esc(foundVal) : '<span class="text-muted fst-italic">— nothing found —</span>');

		// Smart default: empty current value -> default to overwrite;
		// filled current value -> default to keep.
		const defaultOn = !currentVal && !!foundVal;

		return `
			<div class="row g-2 align-items-center py-1 border-bottom compare-row" data-field="${fieldKey}">
				<div class="col-2 small fw-bold text-uppercase">${esc(label)}</div>
				<div class="col-4 small">${currentDisplay}</div>
				<div class="col-4 small">${foundDisplay}</div>
				<div class="col-2 form-check form-switch mb-0">
					<input class="form-check-input overwrite-toggle" type="checkbox" ${defaultOn ? 'checked' : ''} ${foundVal ? '' : 'disabled'}>
					<label class="form-check-label small">Use new</label>
				</div>
			</div>
		`;
	}

	function renderUpdateFields(g) {
		if (!g.currentToyData) {
			return '<div class="text-muted small py-3"><i class="fa-solid fa-spinner fa-spin me-2"></i>Loading current data...</div>';
		}
		const cur = g.currentToyData.toy;
		const lookupName = (list, id) => (list.find((o) => String(o.id) === String(id)) || {}).name || '';

		const rows = [
			compareRow('name', 'Name', cur.name, g.merged.name, !!g.conflicts.name, g),
			compareRow('year_released', 'Year', cur.year_released, g.merged.year, !!g.conflicts.year, g),
			compareRow('wave', 'Wave', cur.wave, g.merged.wave, !!g.conflicts.wave, g),
			compareRow('assortment_sku', 'SKU', cur.assortment_sku, g.merged.assortmentSku, !!g.conflicts.assortmentSku, g),
			compareRow('manufacturer_id', 'Manufacturer', lookupName(IMPORTER_LOOKUPS.manufacturers, cur.manufacturer_id), lookupName(IMPORTER_LOOKUPS.manufacturers, g.merged.manufacturer_id), !!g.conflicts.manufacturer_id, g),
			compareRow('toy_line_id', 'Toy Line', lookupName(IMPORTER_LOOKUPS.toyLines, cur.toy_line_id), lookupName(IMPORTER_LOOKUPS.toyLines, g.merged.toy_line_id), !!g.conflicts.toy_line_id, g),
			compareRow('universe_id', 'Universe', lookupName(IMPORTER_LOOKUPS.universes, cur.universe_id), lookupName(IMPORTER_LOOKUPS.universes, g.merged.universe_id), false, g),
			compareRow('description', 'Description', cur.description, g.merged.description, false, g),
		].join('');

		const accCount = g.accessories.length;
		const imgCount = g.images.length;

		return `
			<div class="row g-2 py-1 border-bottom">
				<div class="col-2 small fw-bold text-uppercase text-muted">Field</div>
				<div class="col-4 small fw-bold text-uppercase text-muted">Currently</div>
				<div class="col-4 small fw-bold text-uppercase text-muted">Found now</div>
				<div class="col-2 small fw-bold text-uppercase text-muted">Action</div>
			</div>
			${rows}
			<div class="small text-muted mt-2">
				<i class="fa-solid fa-plus-circle me-1"></i>
				${accCount} accessor${accCount === 1 ? 'y' : 'ies'} and ${imgCount} photo(s) found will be <strong>added</strong>
				(you have ${g.currentToyData.existingAccessories.length} accessory record(s) and ${g.currentToyData.existingImageCount} photo(s) already — nothing existing is removed).
			</div>
		`;
	}

	function renderGroupCard(g) {
		const isUpdate = g.target.mode === 'update';
		const badge = isUpdate
			? `<span class="badge bg-info">UPDATE EXISTING</span>`
			: `<span class="badge bg-success">NEW</span>`;

		const firstImg = g.images[0];
		const imgHtml = firstImg
			? `<img src="${esc(firstImg)}" class="img-fluid rounded-start h-100" style="object-fit: contain; max-height: 220px; width: 100%; background: #f8f9fa;">`
			: `<div class="d-flex align-items-center justify-content-center bg-light h-100" style="min-height: 140px;"><span class="text-muted"><i class="fa-solid fa-image fa-2x"></i></span></div>`;

		const sourceTags = g.urlResults
			.map((r) => `<span class="badge bg-light text-dark border me-1">${esc(r.source_name)}</span>`)
			.join('');

		const accessoriesHtml = g.accessories.length
			? g.accessories.map((a) => `<span class="badge bg-light text-dark border me-1 mb-1">${esc(a)}</span>`).join('')
			: '<span class="text-muted fst-italic small">None detected</span>';

		const atCap = g.urlResults.length >= MAX_SOURCES_PER_GROUP;

		return `
			<div class="card shadow-sm import-group mb-3" data-group-id="${g.id}">
				<div class="row g-0">
					<div class="col-md-2 border-end">${imgHtml}</div>
					<div class="col-md-10">
						<div class="card-header d-flex justify-content-between align-items-center bg-transparent py-2">
							<div>${badge} <span class="ms-2">${sourceTags}</span></div>
							<div class="d-flex align-items-center gap-3">
								<div class="form-check form-switch mb-0">
									<input class="form-check-input group-include" type="checkbox" ${g.included ? 'checked' : ''}>
									<label class="form-check-label fw-bold small">Include</label>
								</div>
								<button class="btn btn-sm btn-outline-danger btn-remove-group" title="Remove this item"><i class="fa-solid fa-trash-can"></i></button>
							</div>
						</div>
						<div class="card-body py-2">

							<div class="d-flex gap-2 mb-2">
								<input type="text" class="form-control form-control-sm add-source-input" placeholder="Paste another source's URL for this same toy...">
								<button class="btn btn-sm btn-outline-secondary btn-add-source-to-group" ${atCap ? 'disabled' : ''}>
									<i class="fa-solid fa-link me-1"></i>Add source
								</button>
							</div>
							${atCap ? `<div class="small text-muted mb-2">Reached the ${MAX_SOURCES_PER_GROUP}-source limit for one toy.</div>` : ''}

							<div class="mb-2">
								<input type="text" class="form-control form-control-sm attach-search-input" placeholder="Or search your catalog to attach this import to an existing toy...">
								<div class="attach-search-results list-group mt-1"></div>
							</div>
							${isUpdate ? `<div class="small mb-2"><i class="fa-solid fa-link me-1"></i>Attached to <strong>${esc(g.target.toyLabel)}</strong> — <a href="#" class="detach-link">use as new toy instead</a></div>` : ''}

							${isUpdate ? renderUpdateFields(g) : renderCreateFields(g)}

							<div class="mt-2 pt-2 border-top">
								<small class="text-uppercase text-muted fw-bold">Accessories detected</small>
								<div class="mt-1">${accessoriesHtml}</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		`;
	}

	function renderAll() {
		const itemCountEl = document.getElementById('itemCount');
		if (itemCountEl) itemCountEl.textContent = String(groups.length);

		if (groups.length === 0) {
			queueEl.innerHTML = '';
			queueEmpty.classList.remove('d-none');
			return;
		}
		queueEmpty.classList.add('d-none');
		queueEl.innerHTML = groups.map((g) => renderGroupCard(g)).join('');
	}

	// ---------------------------------------------------------------
	// Queue-level event delegation (cards are re-rendered often, so we
	// bind once on the container rather than per-card).
	// ---------------------------------------------------------------

	queueEl.addEventListener('click', async (e) => {
		const card = e.target.closest('.import-group');
		if (!card) return;
		const groupId = parseInt(card.dataset.groupId, 10);
		const g = groups.find((x) => x.id === groupId);
		if (!g) return;

		if (e.target.closest('.btn-remove-group')) {
			groups = groups.filter((x) => x.id !== groupId);
			renderAll();
			return;
		}

		if (e.target.closest('.btn-add-source-to-group')) {
			const input = card.querySelector('.add-source-input');
			const url = input.value.trim();
			if (!url) return;
			const btn = e.target.closest('.btn-add-source-to-group');
			btn.disabled = true;
			try {
				const result = await analyzeUrl(url, 0);
				if (!result.success) {
					UiHelper.showError(result.error || 'Failed to analyze URL');
					return;
				}
				if (result.data.length !== 1) {
					UiHelper.showError('This looks like a listing page with multiple items — use the main "Add Sources" box above instead, it creates separate entries per item.');
					return;
				}
				g.urlResults.push(result.data[0]);
				recomputeMerge(g);
				autoDetectTarget(g);
				renderAll();
			} catch (error) {
				UiHelper.showError(error.message || 'Failed to analyze URL');
			} finally {
				btn.disabled = false;
			}
			return;
		}

		if (e.target.closest('.detach-link')) {
			e.preventDefault();
			setGroupTarget(g, 'create', null, '');
			renderAll();
			return;
		}

		const resultItem = e.target.closest('.attach-search-results .list-group-item');
		if (resultItem) {
			e.preventDefault();
			setGroupTarget(g, 'update', parseInt(resultItem.dataset.id, 10), resultItem.dataset.label);
			renderAll();
			return;
		}
	});

	queueEl.addEventListener('change', (e) => {
		const card = e.target.closest('.import-group');
		if (!card) return;
		const groupId = parseInt(card.dataset.groupId, 10);
		const g = groups.find((x) => x.id === groupId);
		if (!g) return;

		if (e.target.classList.contains('group-include')) {
			g.included = e.target.checked;
		}
	});

	queueEl.addEventListener(
		'input',
		UiHelper.debounce(async (e) => {
			if (!e.target.classList.contains('attach-search-input')) return;
			const card = e.target.closest('.import-group');
			const groupId = parseInt(card.dataset.groupId, 10);
			const g = groups.find((x) => x.id === groupId);
			const q = e.target.value.trim();
			const resultsEl = card.querySelector('.attach-search-results');

			if (q.length < 2) {
				resultsEl.innerHTML = '';
				return;
			}
			const res = await searchCatalog(q);
			const items = res.data || [];
			resultsEl.innerHTML = items
				.map(
					(t) =>
						`<a href="#" class="list-group-item list-group-item-action py-1 small" data-id="${t.id}" data-label="${esc(t.name)}">${esc(t.name)} <span class="text-muted">${t.year_released ? '(' + t.year_released + ')' : ''} ${esc(t.universe_name || '')} ${esc(t.manufacturer_name || '')}</span></a>`,
				)
				.join('') || '<div class="list-group-item py-1 small text-muted">No matches</div>';
		}, 300),
	);

	// ---------------------------------------------------------------
	// Select all / deselect all
	// ---------------------------------------------------------------

	btnSelectAll.addEventListener('click', () => {
		groups.forEach((g) => (g.included = true));
		renderAll();
	});
	btnDeselectAll.addEventListener('click', () => {
		groups.forEach((g) => (g.included = false));
		renderAll();
	});

	// ---------------------------------------------------------------
	// Read live edited values out of a rendered card back into a
	// submission-ready payload.
	// ---------------------------------------------------------------

	function collectGroupPayload(g) {
		const card = queueEl.querySelector(`.import-group[data-group-id="${g.id}"]`);
		const sources = g.urlResults.map((r) => ({
			source_id: r.source_id,
			externalId: r.externalId,
			externalUrl: r.externalUrl,
		}));

		if (g.target.mode === 'update') {
			// compare-row field keys are DB column names; g.merged keys are
			// mostly the same except these two.
			const mergedKeyFor = { year_released: 'year', assortment_sku: 'assortmentSku' };
			const fields = {};
			card.querySelectorAll('.compare-row').forEach((row) => {
				const toggle = row.querySelector('.overwrite-toggle');
				if (!toggle || !toggle.checked) return;
				const fieldKey = row.dataset.field;
				const pick = row.querySelector('.conflict-pick');
				fields[fieldKey] = pick ? pick.value : g.merged[mergedKeyFor[fieldKey] || fieldKey];
			});

			return {
				mode: 'update',
				targetCatalogToyId: g.target.catalogToyId,
				fields: { name: fields.name || g.merged.name, ...fields },
				accessories: g.accessories,
				images: g.images,
				sources,
			};
		}

		const val = (sel) => card.querySelector(sel)?.value ?? '';
		const pickVal = (fieldKey, fallback) => {
			const pick = card.querySelector(`.conflict-pick[data-field="${fieldKey}"]`);
			return pick ? pick.value : fallback;
		};

		return {
			mode: 'create',
			targetCatalogToyId: null,
			fields: {
				name: val('.field-name'),
				year_released: pickVal('year', val('.field-year')),
				wave: pickVal('wave', val('.field-wave')),
				assortment_sku: pickVal('assortmentSku', val('.field-sku')),
				universe_id: val('.field-universe') || null,
				manufacturer_id: pickVal('manufacturer_id', val('.field-manufacturer')) || null,
				toy_line_id: pickVal('toy_line_id', val('.field-toy-line')) || null,
				product_type_id: val('.field-product-type') || null,
				entertainment_source_id: val('.field-entertainment-source') || null,
				description: val('.field-description'),
			},
			accessories: g.accessories,
			images: g.images,
			sources,
		};
	}

	// ---------------------------------------------------------------
	// Run Import
	// ---------------------------------------------------------------

	btnRunImport.addEventListener('click', async () => {
		const included = groups.filter((g) => g.included);
		if (included.length === 0) {
			UiHelper.showError('No items selected');
			return;
		}

		const payloads = included.map((g) => ({ g, payload: collectGroupPayload(g) }));

		const missingToyLine = payloads.filter(
			({ payload }) => payload.mode === 'create' && !payload.fields.toy_line_id,
		);
		if (missingToyLine.length > 0) {
			UiHelper.showError(
				`${missingToyLine.length} selected item(s) have no Toy Line set — pick one before importing: ${missingToyLine.map((p) => p.payload.fields.name).join(', ')}`,
			);
			return;
		}

		if (!confirm(`Import ${included.length} item(s) into the catalog?`)) return;

		btnRunImport.disabled = true;
		const originalHtml = btnRunImport.innerHTML;
		btnRunImport.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Importing...';

		try {
			const csrfMeta = document.querySelector('meta[name="csrf-token"]');
			const result = await ApiClient.request(baseUrl + 'importer-run/import', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-Token': csrfMeta ? csrfMeta.content : '',
				},
				body: JSON.stringify({ groups: payloads.map((p) => p.payload) }),
			});

			if (result.success) {
				UiHelper.showSuccess(`Successfully imported ${result.count} item(s)!`);
				if (result.errors && result.errors.length > 0) {
					result.errors.forEach((err) => UiHelper.showError(err));
				}
				groups = [];
				renderAll();
			} else {
				UiHelper.showError(result.error || 'Import failed');
			}
		} catch (error) {
			UiHelper.showError(error.message || 'Import failed');
		} finally {
			btnRunImport.disabled = false;
			btnRunImport.innerHTML = originalHtml;
		}
	});

	function esc(str) {
		if (str === null || str === undefined) return '';
		const div = document.createElement('div');
		div.textContent = String(str);
		return div.innerHTML;
	}

	renderAll();
});

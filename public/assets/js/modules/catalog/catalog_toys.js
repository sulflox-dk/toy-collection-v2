// Catalog Wizard Manager
const CatalogWizard = {
	modal: null,
	contentContainer: null,
	itemCount: 0,
	meta: {},
	currentMediaType: null,
	currentMediaId: null,

	init() {
		this.modal = new bootstrap.Modal(document.getElementById('entity-modal'));
		this.contentContainer = document.getElementById('entity-modal-content');

		const btnAdd = document.getElementById('btn-add-catalog-toy');
		if (btnAdd) {
			const newBtnAdd = btnAdd.cloneNode(true);
			btnAdd.parentNode.replaceChild(newBtnAdd, btnAdd);
			newBtnAdd.addEventListener('click', () => this.loadStep1());
		}
	},

	editToy(toyId, universeId) {
		this.modal.show();
		this.goToStep2(universeId, toyId);
	},

	editPhotos(toyId) {
		this.modal.show();
		this.goToStep3(toyId);
	},

	async loadStep1() {
		this.contentContainer.innerHTML =
			'<div class="p-5 text-center"><i class="fa-solid fa-spinner fa-spin fa-3x text-muted"></i></div>';
		this.modal.show();

		try {
			const response = await fetch(SITE_URL + 'catalog-toy/create-step-1');
			if (!response.ok) throw new Error('Network response was not ok');
			this.contentContainer.innerHTML = await response.text();
		} catch (error) {
			this.contentContainer.innerHTML =
				'<div class="p-4 text-danger">Failed to load universes.</div>';
		}
	},

	async goToStep2(universeId, toyId = null) {
		this.contentContainer.innerHTML =
			'<div class="p-5 text-center"><i class="fa-solid fa-spinner fa-spin fa-3x text-muted"></i></div>';

		try {
			// Append the ID to the URL if we are editing
			let url = `${SITE_URL}catalog-toy/create-step-2?universe_id=${universeId}`;
			if (toyId) url += `&id=${toyId}`;

			const response = await fetch(url);
			if (!response.ok) throw new Error('Network response was not ok');
			this.contentContainer.innerHTML = await response.text();

			this.initCascadingDropdowns();
			this.initItemsManager();
		} catch (error) {
			this.contentContainer.innerHTML =
				'<div class="p-4 text-danger">Failed to load form.</div>';
		}
	},

	// --- CASCADING DROPDOWN LOGIC ---
	initCascadingDropdowns() {
		const form = document.getElementById('catalogToyForm');
		if (!form) return;

		// Load JSON metadata from data attributes
		this.meta = {
			manufacturers: JSON.parse(form.dataset.manufacturers || '[]'),
			toyLines: JSON.parse(form.dataset.toyLines || '[]'),
			sources: JSON.parse(form.dataset.sources || '[]'),
			subjects: JSON.parse(form.dataset.subjects || '[]'),
		};

		const uniSelect = document.getElementById('catalog_universe_id');
		const manSelect = document.getElementById('catalog_manufacturer_id');

		// Add Listeners
		uniSelect.addEventListener('change', () =>
			this.handleUniverseChange(false),
		);
		manSelect.addEventListener('change', () =>
			this.handleManufacturerChange(false),
		);

		// Trigger initial state
		this.handleUniverseChange(true);
	},

	handleUniverseChange(isInit = false) {
		const univId =
			parseInt(document.getElementById('catalog_universe_id').value) || 0;
		const sourceSelect = document.getElementById(
			'catalog_entertainment_source_id',
		);
		const manfSelect = document.getElementById('catalog_manufacturer_id');

		// 1. Update Entertainment Sources (Linked to Universe OR null/global)
		const validSources = this.meta.sources.filter(
			(s) => s.universe_id == univId || s.universe_id === null,
		);
		this.populateSelect(
			sourceSelect,
			validSources,
			isInit ? sourceSelect.dataset.selected : '',
			'None / Select Source...',
			(s) => `${s.name} (${s.type})`,
		);
		sourceSelect.disabled = univId === 0;

		// 2. Update Manufacturers (Only those that have Toy Lines in this Universe)
		const validManfIds = new Set(
			this.meta.toyLines
				.filter((tl) => tl.universe_id == univId)
				.map((tl) => tl.manufacturer_id),
		);
		const validManufacturers = this.meta.manufacturers.filter((m) =>
			validManfIds.has(m.id),
		);

		this.populateSelect(
			manfSelect,
			validManufacturers,
			isInit ? manfSelect.dataset.selected : '',
			'Select Manufacturer...',
		);
		manfSelect.disabled = univId === 0;

		// 3. Sync Item Subject dropdowns if any exist
		this.updateItemSubjects();

		// 4. Cascade to Manufacturer change
		this.handleManufacturerChange(isInit);
	},

	handleManufacturerChange(isInit = false) {
		const univId =
			parseInt(document.getElementById('catalog_universe_id').value) || 0;
		const manfId =
			parseInt(document.getElementById('catalog_manufacturer_id').value) ||
			0;
		const toyLineSelect = document.getElementById('catalog_toy_line_id');

		// Update Toy Lines (Requires BOTH Universe and Manufacturer)
		const validLines = this.meta.toyLines.filter(
			(tl) => tl.universe_id == univId && tl.manufacturer_id == manfId,
		);

		this.populateSelect(
			toyLineSelect,
			validLines,
			isInit ? toyLineSelect.dataset.selected : '',
			'Select Toy Line...',
		);
		toyLineSelect.disabled = univId === 0 || manfId === 0;
	},

	populateSelect(
		selectElement,
		dataArray,
		selectedValue,
		placeholder,
		textCallback = null,
	) {
		const currentVal =
			selectedValue !== '' ? selectedValue : selectElement.value;
		selectElement.innerHTML = `<option value="">${placeholder}</option>`;

		dataArray.forEach((item) => {
			const opt = document.createElement('option');
			opt.value = item.id;
			opt.text = textCallback ? textCallback(item) : item.name;
			if (item.id == currentVal) opt.selected = true;
			selectElement.appendChild(opt);
		});
	},

	// --- ITEM & SUBJECT LOGIC ---
	updateItemSubjects() {
		const univId =
			parseInt(document.getElementById('catalog_universe_id').value) || 0;
		const validSubjects = this.meta.subjects.filter(
			(s) => s.universe_id == univId,
		);
		const validSubjectIds = new Set(validSubjects.map((s) => parseInt(s.id)));

		// If the universe changes, clear out any selected subjects that are no longer valid
		document.querySelectorAll('.item-row').forEach((row) => {
			const input = row.querySelector('.item-subject-id');
			if (input.value && !validSubjectIds.has(parseInt(input.value))) {
				input.value = '';
				row.querySelector('.subject-name').textContent =
					'Select Subject...';
				row.querySelector('.subject-meta').style.display = 'none';
			}
		});
	},

	initItemsManager() {
		this.itemCount = document.querySelectorAll('.item-row').length;
		this.updateItemCountBadge();
	},

	addEmptyItemRow() {
		const container = document.getElementById('itemsContainer');
		const template = document.getElementById('itemRowTemplate');

		const clone = template.content.cloneNode(true);
		const currentIndex = this.itemCount++;

		clone.querySelectorAll('input, select').forEach((input) => {
			if (input.name) {
				input.name = input.name.replace('{INDEX}', currentIndex);
			}
		});

		container.appendChild(clone);
		this.updateItemSubjects();
		this.updateItemCountBadge();
	},

	removeItemRow(buttonElement) {
		const row = buttonElement.closest('.item-row');
		if (row) {
			row.remove();
			this.updateItemCountBadge();
		}
	},

	updateItemCountBadge() {
		const count = document.querySelectorAll('.item-row').length;
		const badge = document.getElementById('itemCountBadge');
		if (badge) {
			badge.textContent = `${count} item${count === 1 ? '' : 's'}`;
		}
	},

	toggleSubjectSearch(element) {
		const wrapper = element.closest('.subject-selector-wrapper');
		const dropdown = wrapper.querySelector('.subject-search-dropdown');
		const input = dropdown.querySelector('.search-input');

		// Close all other open dropdowns first
		document.querySelectorAll('.subject-search-dropdown').forEach((dd) => {
			if (dd !== dropdown) dd.classList.add('d-none');
		});

		dropdown.classList.toggle('d-none');
		if (!dropdown.classList.contains('d-none')) {
			input.value = ''; // Clear previous search
			input.focus();
			this.renderSubjectResults(wrapper); // Render all valid subjects initially
		}
	},

	filterSubjects(inputEl) {
		const wrapper = inputEl.closest('.subject-selector-wrapper');
		this.renderSubjectResults(wrapper, inputEl.value.toLowerCase());
	},

	renderSubjectResults(wrapper, query = '') {
		const resultsContainer = wrapper.querySelector('.results-list');
		const univId =
			parseInt(document.getElementById('catalog_universe_id').value) || 0;

		let validSubjects = this.meta.subjects.filter(
			(s) => s.universe_id == univId,
		);

		if (query) {
			validSubjects = validSubjects.filter(
				(s) =>
					s.name.toLowerCase().includes(query) ||
					s.type.toLowerCase().includes(query),
			);
		}

		resultsContainer.innerHTML = '';

		if (validSubjects.length === 0) {
			resultsContainer.innerHTML =
				'<div class="p-3 text-muted small text-center">No subjects found.</div>';
			return;
		}

		validSubjects.forEach((sub) => {
			const div = document.createElement('div');
			div.className = 'p-2 border-bottom subject-result-item bg-white';
			div.style.cursor = 'pointer';
			div.innerHTML = `<div class="fw-bold small text-dark">${sub.name}</div><div class="text-muted" style="font-size:0.7rem;">${sub.type}</div>`;
			div.onclick = () => this.selectSubject(wrapper, sub);
			resultsContainer.appendChild(div);
		});
	},

	selectSubject(wrapper, sub) {
		const row = wrapper.closest('.item-row');
		row.querySelector('.item-subject-id').value = sub.id;
		wrapper.querySelector('.subject-name').textContent = sub.name;
		const meta = wrapper.querySelector('.subject-meta');
		meta.textContent = sub.type;
		meta.style.display = 'block';
		wrapper.querySelector('.subject-search-dropdown').classList.add('d-none');
	},

	// --- FORM SUBMISSION ---
	async submitStep2() {
		const form = document.getElementById('catalogToyForm');
		if (!form.checkValidity()) {
			form.reportValidity();
			return;
		}

		const submitBtn = form.querySelector('button[type="submit"]');
		const originalBtnHtml = submitBtn.innerHTML;
		submitBtn.innerHTML =
			'<i class="fa-solid fa-spinner fa-spin me-2"></i> Saving...';
		submitBtn.disabled = true;

		try {
			const formData = new FormData(form);
			const response = await fetch(SITE_URL + 'catalog-toy/store', {
				method: 'POST',
				body: formData,
			});

			const result = await response.json();

			if (result.success) {
				// --- BUG 1 FIX ---
				// Check if the form has an ID (meaning it's an Edit)
				const isEdit = formData.has('id') && formData.get('id') !== '';

				if (isEdit) {
					// We are editing: close modal and refresh grid
					this.modal.hide();
					if (window.catalogToyManager) {
						window.catalogToyManager.loadList();
					}
				} else {
					// We are creating: proceed to Step 3
					this.goToStep3(result.id);
				}
				// -----------------
			} else {
				alert('Error saving toy: ' + result.message);
				submitBtn.innerHTML = originalBtnHtml;
				submitBtn.disabled = false;
			}
		} catch (error) {
			console.error('Save failed', error);
			alert('An unexpected network error occurred.');
			submitBtn.innerHTML = originalBtnHtml;
			submitBtn.disabled = false;
		}
	},

	async goToStep3(toyId) {
		this.contentContainer.innerHTML =
			'<div class="p-5 text-center"><i class="fa-solid fa-spinner fa-spin fa-3x text-muted mb-3"></i><h5>Preparing Image Manager...</h5></div>';

		try {
			const response = await fetch(
				`${SITE_URL}catalog-toy/create-step-3?id=${toyId}`,
			);
			if (!response.ok) throw new Error('Network response was not ok');

			this.contentContainer.innerHTML = await response.text();

			this.currentMediaType = null;
			this.currentMediaId = null;

			this.refreshThumbnails('catalog_toys', toyId);

			document
				.querySelectorAll('[id^="preview-catalog_toy_items-"]')
				.forEach((container) => {
					const itemId = container.id.split('-').pop();
					this.refreshThumbnails('catalog_toy_items', itemId);
				});

			// --- ADD THIS LINE ---
			this.initDragAndDrop();
		} catch (error) {
			console.error('Failed to load Step 3', error);
			this.contentContainer.innerHTML =
				'<div class="p-4 text-danger">Failed to load Image Manager.</div>';
		}
	},

	// --- DRAG AND DROP LOGIC ---
	initDragAndDrop() {
		const dropZone = document.getElementById('mediaDropZone');
		const fileInput = document.getElementById('mediaUploadInput');

		if (!dropZone || !fileInput) return;

		// 1. Prevent default browser behavior (opening the file)
		['dragenter', 'dragover', 'dragleave', 'drop'].forEach((eventName) => {
			dropZone.addEventListener(
				eventName,
				(e) => {
					e.preventDefault();
					e.stopPropagation();
				},
				false,
			);
		});

		// 2. Highlight the drop zone when dragging over it
		['dragenter', 'dragover'].forEach((eventName) => {
			dropZone.addEventListener(
				eventName,
				() => {
					dropZone.classList.add('bg-light', 'border-primary');
				},
				false,
			);
		});

		// 3. Remove highlight when dragging away or dropping
		['dragleave', 'drop'].forEach((eventName) => {
			dropZone.addEventListener(
				eventName,
				() => {
					dropZone.classList.remove('bg-light', 'border-primary');
				},
				false,
			);
		});

		// 4. Handle the actual drop!
		dropZone.addEventListener(
			'drop',
			(e) => {
				const dt = e.dataTransfer;
				const files = dt.files;

				if (files && files.length > 0) {
					// Assign the dropped files to the hidden input
					fileInput.files = files;
					// Trigger the existing upload method
					this.handleFileUpload(fileInput);
				}
			},
			false,
		);
	},

	// --- MEDIA PICKER LOGIC ---
	openMediaPicker(entityType, entityId) {
		this.currentMediaType = entityType;
		this.currentMediaId = entityId;

		const overlay = document.getElementById('mediaPickerOverlay');
		if (overlay) {
			overlay.classList.remove('d-none');
			overlay.classList.add('d-flex');
		}
	},

	closeMediaPicker() {
		const overlay = document.getElementById('mediaPickerOverlay');
		if (overlay) {
			overlay.classList.remove('d-flex');
			overlay.classList.add('d-none');
		}

		// Added safe checks so it never crashes if an element is missing
		const fileInput = document.getElementById('mediaUploadInput');
		if (fileInput) fileInput.value = '';

		const searchInput = document.getElementById('mediaSearchInput');
		if (searchInput) searchInput.value = '';

		const searchResults = document.getElementById('mediaSearchResults');
		if (searchResults) {
			searchResults.innerHTML =
				'<div class="text-center text-muted mt-5">Type to search your image library...</div>';
		}
	},

	async handleFileUpload(inputElement) {
		const files = inputElement.files;
		if (files.length === 0) return;

		const dropZone = document.getElementById('mediaDropZone');

		// 1. Hide the existing content safely without deleting it
		const originalDisplayStates = [];
		Array.from(dropZone.children).forEach((child) => {
			originalDisplayStates.push({
				el: child,
				display: child.style.display,
			});
			child.style.display = 'none';
		});

		// 2. Add the spinner
		const spinner = document.createElement('div');
		spinner.id = 'uploadSpinner';
		spinner.className =
			'd-flex flex-column align-items-center justify-content-center h-100 text-muted';
		spinner.innerHTML =
			'<i class="fa-solid fa-spinner fa-spin fa-3x mb-3"></i><h5>Uploading...</h5>';
		dropZone.appendChild(spinner);

		const csrfToken =
			document
				.querySelector('meta[name="csrf-token"]')
				?.getAttribute('content') || '';

		try {
			for (let i = 0; i < files.length; i++) {
				const formData = new FormData();
				formData.append('file', files[i]);
				formData.append('entity_type', this.currentMediaType);
				formData.append('entity_id', this.currentMediaId);

				const response = await fetch(SITE_URL + 'media-file', {
					method: 'POST',
					headers: { 'X-CSRF-TOKEN': csrfToken },
					body: formData,
				});

				if (!response.ok) {
					const err = await response.json();
					console.error('Upload error:', err);
				}
			}

			this.closeMediaPicker();
			this.refreshThumbnails(this.currentMediaType, this.currentMediaId);
		} catch (error) {
			console.error(error);
			alert('Upload failed due to a network error.');
		} finally {
			// 3. Remove spinner and restore original UI
			const activeSpinner = document.getElementById('uploadSpinner');
			if (activeSpinner) activeSpinner.remove();

			originalDisplayStates.forEach((item) => {
				item.el.style.display = item.display;
			});
		}
	},

	async searchLibrary(query) {
		const resultsContainer = document.getElementById('mediaSearchResults');

		if (query.length < 2) {
			resultsContainer.innerHTML =
				'<div class="text-center text-muted mt-5">Type to search your image library...</div>';
			return;
		}

		resultsContainer.innerHTML =
			'<div class="text-center text-muted mt-5"><i class="fa-solid fa-spinner fa-spin me-2"></i>Searching...</div>';

		try {
			const response = await fetch(
				`${SITE_URL}media-file/search-json?q=${encodeURIComponent(query)}`,
			);
			const data = await response.json();

			if (data.length === 0) {
				resultsContainer.innerHTML =
					'<div class="text-center text-muted mt-5">No images found matching your search.</div>';
				return;
			}

			let html = '<div class="row g-2">';
			data.forEach((file) => {
				const title = file.title || file.filename;
				html += `
                <div class="col-4 col-md-3">
                    <div class="card h-100 border shadow-sm" style="cursor: pointer; transition: transform 0.1s;" 
                         onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'"
                         onclick="CatalogWizard.linkExistingMedia(${file.id})">
                        <img src="${SITE_URL}${file.filepath}" class="card-img-top" style="height: 100px; object-fit: contain; padding: 5px;" loading="lazy">
                        <div class="card-body p-1 text-center text-truncate small text-muted bg-light" style="font-size: 0.7rem;" title="${title}">
                            ${title}
                        </div>
                    </div>
                </div>`;
			});
			html += '</div>';
			resultsContainer.innerHTML = html;
		} catch (error) {
			resultsContainer.innerHTML =
				'<div class="text-danger mt-5 text-center">Search failed to load.</div>';
		}
	},

	async linkExistingMedia(mediaFileId) {
		const csrfToken =
			document
				.querySelector('meta[name="csrf-token"]')
				?.getAttribute('content') || '';

		const formData = new FormData();
		formData.append('media_file_id', mediaFileId);
		formData.append('entity_type', this.currentMediaType);
		formData.append('entity_id', this.currentMediaId);

		try {
			const response = await fetch(SITE_URL + 'media-file/link', {
				method: 'POST',
				headers: { 'X-CSRF-TOKEN': csrfToken },
				body: formData,
			});

			const result = await response.json();
			if (result.success) {
				this.closeMediaPicker();
				this.refreshThumbnails(this.currentMediaType, this.currentMediaId);
			}
		} catch (error) {
			console.error('Failed to link media', error);
		}
	},

	async refreshThumbnails(entityType, entityId) {
		const container = document.getElementById(
			`preview-${entityType}-${entityId}`,
		);
		if (!container) return;

		container.innerHTML =
			'<div class="w-100 text-center py-3"><i class="fa-solid fa-spinner fa-spin text-muted"></i></div>';

		try {
			const response = await fetch(
				`${SITE_URL}media-file/thumbnails?type=${entityType}&id=${entityId}`,
			);
			const images = await response.json();

			if (images.length === 0) {
				container.innerHTML =
					'<div class="text-muted small w-100 text-center py-3 bg-light border rounded border-dashed">No images attached yet.</div>';
				return;
			}

			const successAlert = document.getElementById('step3-success-alert');
			if (successAlert) {
				successAlert.style.opacity = '0';
				setTimeout(() => successAlert.remove(), 300);
			}

			container.innerHTML = ''; // Clear spinner

			const wrapper = document.createElement('div');
			wrapper.className = 'vstack gap-3 mt-3 w-100';

			const template = document.getElementById('mediaEditRowTemplate');

			images.forEach((img) => {
				const clone = template.content.cloneNode(true);
				const row = clone.querySelector('.media-edit-row');

				row.dataset.mediaId = img.media_file_id;

				// Populate data into the HTML template
				clone.querySelector('.preview-img').src = SITE_URL + img.filepath;
				clone.querySelector('.meta-title').value = img.title || '';
				clone.querySelector('.meta-alt').value = img.alt_text || '';
				clone.querySelector('.meta-desc').value = img.description || '';

				// Build Tags
				clone.querySelector('.tag-container').innerHTML =
					this.buildTagPills(img.tag_ids || []);

				// Unlink button logic
				clone.querySelector('.btn-unlink').onclick = () => {
					this.unlinkMedia(img.link_id, entityType, entityId);
				};

				// Attach Auto-save listeners
				this.attachMediaRowListeners(row, img.media_file_id);

				wrapper.appendChild(clone);
			});

			container.appendChild(wrapper);
		} catch (error) {
			container.innerHTML =
				'<div class="text-danger small w-100 text-center">Error loading images</div>';
		}
	},

	buildTagPills(activeTagIds) {
		const container = document.getElementById('media-manager-container');
		const tagsJson = container.dataset.tags || '[]';
		let allTags = [];
		try {
			allTags = JSON.parse(tagsJson);
		} catch (e) {}

		if (allTags.length === 0)
			return '<span class="text-muted small">No tags available.</span>';

		const activeIds = activeTagIds.map((id) => parseInt(id));

		return allTags
			.map((tag) => {
				const isActive = activeIds.includes(parseInt(tag.id));
				const bgClass = isActive
					? 'bg-dark text-white'
					: 'bg-light text-dark border';
				return `
                <span class="badge rounded-pill ${bgClass} tag-pill p-2" 
                      data-id="${tag.id}" 
                      style="cursor: pointer; user-select: none; transition: all 0.2s;">
                    ${tag.name}
                </span>`;
			})
			.join('');
	},

	attachMediaRowListeners(rowElement, mediaId) {
		// Tag toggling logic
		rowElement.querySelectorAll('.tag-pill').forEach((pill) => {
			pill.addEventListener('click', () => {
				if (pill.classList.contains('bg-dark')) {
					pill.classList.replace('bg-dark', 'bg-light');
					pill.classList.replace('text-white', 'text-dark');
					pill.classList.add('border');
				} else {
					pill.classList.replace('bg-light', 'bg-dark');
					pill.classList.replace('text-dark', 'text-white');
					pill.classList.remove('border');
				}
				this.saveMediaMetadata(mediaId, rowElement);
			});
		});

		// Save on input change (when clicking out of a text field)
		rowElement.querySelectorAll('.meta-input').forEach((input) => {
			input.addEventListener('change', () => {
				this.saveMediaMetadata(mediaId, rowElement);
			});
		});
	},

	async saveMediaMetadata(mediaId, rowElement) {
		const title = rowElement.querySelector('.meta-title').value;
		const alt = rowElement.querySelector('.meta-alt').value;
		const desc = rowElement.querySelector('.meta-desc').value;

		const activePills = rowElement.querySelectorAll('.tag-pill.bg-dark');
		const selectedTags = Array.from(activePills)
			.map((p) => p.dataset.id)
			.join(',');

		const indicator = rowElement.querySelector('.save-indicator');

		// --- BUG 2 FIX ---
		// Use FormData instead of URLSearchParams to securely package the data
		const formData = new FormData();
		formData.append('_method', 'PUT'); // Framework standard for spoofing PUT
		formData.append('title', title);
		formData.append('alt_text', alt);
		formData.append('description', desc);
		formData.append('tag_ids', selectedTags);

		const csrfToken =
			document
				.querySelector('meta[name="csrf-token"]')
				?.getAttribute('content') || '';

		try {
			// Send as POST so PHP natively populates the $_POST array!
			const response = await fetch(`${SITE_URL}media-file/${mediaId}`, {
				method: 'POST',
				headers: { 'X-CSRF-TOKEN': csrfToken },
				body: formData,
			});

			if (response.ok && indicator) {
				indicator.style.opacity = '1';
				setTimeout(() => (indicator.style.opacity = '0'), 2000);
			}
		} catch (error) {
			console.error('Save metadata failed:', error);
		}
	},

	async unlinkMedia(linkId, entityType, entityId) {
		if (
			!confirm('Are you sure you want to remove this photo from this item?')
		)
			return;

		const csrfToken =
			document
				.querySelector('meta[name="csrf-token"]')
				?.getAttribute('content') || '';
		const formData = new FormData();
		formData.append('link_id', linkId);

		try {
			const response = await fetch(`${SITE_URL}media-file/unlink`, {
				method: 'POST',
				headers: { 'X-CSRF-TOKEN': csrfToken },
				body: formData,
			});

			if (response.ok) {
				this.refreshThumbnails(entityType, entityId);
			}
		} catch (error) {
			console.error('Failed to unlink media:', error);
		}
	},
};

// --- GLOBAL EVENT LISTENERS ---
document.addEventListener('DOMContentLoaded', () => {
	const params = new URLSearchParams(window.location.search);
	let initialView = params.get('view');
	if (!initialView && typeof window.getCookie === 'function') {
		initialView = window.getCookie('catalog-toy_view');
	}
	initialView = initialView || 'list';
	if (typeof window.setViewMode === 'function') {
		window.setViewMode(initialView, false);
	}

	if (typeof EntityManager !== 'undefined') {
		window.catalogToyManager = new EntityManager('catalog-toy', {
			mode: 'html',
			endpoint: '/catalog-toy',
			listUrl: '/catalog-toy/list',
		});
	}

	CatalogWizard.init();
});

document.addEventListener('click', (e) => {
	if (!e.target.closest('.subject-selector-wrapper')) {
		document
			.querySelectorAll('.subject-search-dropdown')
			.forEach((dd) => dd.classList.add('d-none'));
	}
});

// Catalog Wizard Manager
const CatalogWizard = {
	modal: null,
	contentContainer: null,
	itemCount: 0,
	meta: {},

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

			const nameEl = document.createElement('div');
			nameEl.className = 'fw-bold small text-dark';
			nameEl.textContent = sub.name;

			const typeEl = document.createElement('div');
			typeEl.className = 'text-muted';
			typeEl.style.fontSize = '0.7rem';
			typeEl.textContent = sub.type;

			div.appendChild(nameEl);
			div.appendChild(typeEl);
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

			MediaPicker.refreshThumbnails('catalog_toys', toyId);

			document
				.querySelectorAll('[id^="preview-catalog_toy_items-"]')
				.forEach((container) => {
					const itemId = container.id.split('-').pop();
					MediaPicker.refreshThumbnails('catalog_toy_items', itemId);
				});

			MediaPicker.initDragAndDrop();
		} catch (error) {
			console.error('Failed to load Step 3', error);
			this.contentContainer.innerHTML =
				'<div class="p-4 text-danger">Failed to load Image Manager.</div>';
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

// Collection Wizard Manager
const CollectionWizard = {
	modal: null,
	contentContainer: null,

	init() {
		this.modal = new bootstrap.Modal(document.getElementById('entity-modal'));
		this.contentContainer = document.getElementById('entity-modal-content');

		const btnAdd = document.getElementById('btn-add-collection-toy');
		if (btnAdd) {
			const newBtnAdd = btnAdd.cloneNode(true);
			btnAdd.parentNode.replaceChild(newBtnAdd, btnAdd);
			newBtnAdd.addEventListener('click', () => this.loadStep1());
		}
	},

	// --- HELPERS ---

	escapeHtml(str) {
		if (!str) return '';
		const div = document.createElement('div');
		div.textContent = str;
		return div.innerHTML;
	},

	async loadPartial(url, errorMessage, onLoaded) {
		this.contentContainer.innerHTML =
			'<div class="p-5 text-center"><i class="fa-solid fa-spinner fa-spin fa-3x text-muted"></i></div>';

		try {
			const response = await fetch(url);
			if (!response.ok) throw new Error('Network response was not ok');
			this.contentContainer.innerHTML = await response.text();
			if (onLoaded) onLoaded();
		} catch (error) {
			console.error(errorMessage, error);
			this.contentContainer.innerHTML =
				`<div class="p-4 text-danger">${errorMessage}</div>`;
		}
	},

	// --- STEP 1: CATALOG TOY PICKER ---

	async loadStep1() {
		this.modal.show();
		await this.loadPartial(
			SITE_URL + 'collection-toy/create-step-1',
			'Failed to load catalog picker.',
			() => this.initStep1Search(),
		);
	},

	initStep1Search() {
		const input = document.getElementById('catalogToySearch');
		if (!input) return;

		let debounceTimer = null;
		input.addEventListener('input', (e) => {
			clearTimeout(debounceTimer);
			debounceTimer = setTimeout(() => this.searchCatalog(e.target.value.trim()), 300);
		});

		input.focus();
	},

	async searchCatalog(query) {
		const container = document.getElementById('catalogSearchResults');
		if (!container) return;

		if (query.length < 2) {
			container.innerHTML =
				'<div class="text-center text-muted mt-5">' +
				'<i class="fa-solid fa-box-open fa-3x mb-3 opacity-25"></i>' +
				'<p class="mb-0">Start typing to search your catalog...</p></div>';
			return;
		}

		container.innerHTML =
			'<div class="text-center text-muted mt-4"><i class="fa-solid fa-spinner fa-spin fa-2x"></i></div>';

		try {
			const response = await fetch(
				`${SITE_URL}collection-toy/search-catalog?q=${encodeURIComponent(query)}`,
			);
			const results = await response.json();

			if (results.length === 0) {
				container.innerHTML =
					'<div class="text-center text-muted mt-5">' +
					'<i class="fa-solid fa-search fa-2x mb-3 opacity-25"></i>' +
					'<p class="mb-0">No matching catalog toys found.</p></div>';
				return;
			}

			container.innerHTML = results
				.map(
					(toy) => `
				<div class="card mb-2 shadow-sm border-0" onclick="CollectionWizard.goToStep2(${toy.id})" style="cursor: pointer; transition: transform 0.1s;">
					<div class="card-body p-3 d-flex align-items-center gap-3">
						<div class="flex-shrink-0 bg-light border rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
							${
								toy.image_path
									? `<img src="${this.escapeHtml(toy.image_path)}" style="max-width:100%; max-height:100%; object-fit:contain;">`
									: '<i class="fa-solid fa-cube text-muted"></i>'
							}
						</div>
						<div class="flex-grow-1 overflow-hidden">
							<div class="fw-bold text-truncate">${this.escapeHtml(toy.name)}</div>
							<small class="text-muted">${this.escapeHtml(
								[toy.universe_name, toy.toy_line_name, toy.manufacturer_name, toy.year_released]
									.filter(Boolean)
									.join(' · '),
							)}</small>
						</div>
						<i class="fa-solid fa-chevron-right text-muted"></i>
					</div>
				</div>`,
				)
				.join('');
		} catch (error) {
			console.error('Catalog search failed', error);
			container.innerHTML =
				'<div class="text-danger text-center mt-5">Search failed. Please try again.</div>';
		}
	},

	// --- STEP 2: COLLECTION DETAILS FORM ---

	async goToStep2(catalogToyId, collectionToyId = null) {
		let url = `${SITE_URL}collection-toy/create-step-2?catalog_toy_id=${catalogToyId}`;
		if (collectionToyId) url += `&id=${collectionToyId}`;

		await this.loadPartial(url, 'Failed to load form.', () => {
			this.initGradingToggle();
		});
	},

	editToy(collectionToyId) {
		this.modal.show();
		// Pass 0 for catalogToyId — the controller resolves it from the collection toy
		this.goToStep2(0, collectionToyId);
	},

	editPhotos(collectionToyId) {
		this.modal.show();
		this.goToStep3(collectionToyId);
	},

	initGradingToggle() {
		const section = document.getElementById('gradingSection');
		const chevron = document.getElementById('gradingChevron');
		if (!section || !chevron) return;

		section.addEventListener('show.bs.collapse', () => {
			chevron.classList.replace('fa-chevron-right', 'fa-chevron-down');
		});
		section.addEventListener('hide.bs.collapse', () => {
			chevron.classList.replace('fa-chevron-down', 'fa-chevron-right');
		});

		// Sync initial state
		if (section.classList.contains('show')) {
			chevron.classList.replace('fa-chevron-right', 'fa-chevron-down');
		}
	},

	checkAllItems(checked) {
		document.querySelectorAll('#itemsContainer input[type="checkbox"][name*="is_present"]').forEach((cb) => {
			cb.checked = checked;
		});
	},

	async submitStep2() {
		const form = document.getElementById('collectionToyForm');
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
			const response = await fetch(SITE_URL + 'collection-toy/store', {
				method: 'POST',
				body: formData,
			});

			const result = await response.json();

			if (result.success) {
				const isEdit = formData.has('id') && formData.get('id') !== '';

				if (isEdit) {
					this.modal.hide();
					if (window.collectionToyManager) {
						window.collectionToyManager.loadList();
					}
				} else {
					this.goToStep3(result.id);
				}
			} else {
				alert('Error saving: ' + result.message);
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

	// --- STEP 3: MEDIA MANAGER ---

	async goToStep3(collectionToyId) {
		await this.loadPartial(
			`${SITE_URL}collection-toy/create-step-3?id=${collectionToyId}`,
			'Failed to load Image Manager.',
			() => {
				MediaPicker.refreshThumbnails('collection_toys', collectionToyId);

				document
					.querySelectorAll('[id^="preview-collection_toy_items-"]')
					.forEach((container) => {
						const itemId = container.id.split('-').pop();
						MediaPicker.refreshThumbnails('collection_toy_items', itemId);
					});

				MediaPicker.initDragAndDrop();
			},
		);
	},
};

// --- GLOBAL EVENT LISTENERS ---
document.addEventListener('DOMContentLoaded', () => {
	// 1. Handle View Mode (List vs Cards)
	const params = new URLSearchParams(window.location.search);
	let initialView = params.get('view');

	if (!initialView && typeof window.getCookie === 'function') {
		initialView = window.getCookie('collection-toy_view');
	}

	initialView = initialView || 'cards';

	if (typeof window.setViewMode === 'function') {
		window.setViewMode(initialView, false);
	}

	// 2. Initialize the Entity Manager
	if (typeof EntityManager !== 'undefined') {
		window.collectionToyManager = new EntityManager('collection-toy', {
			mode: 'html',
			endpoint: '/collection-toy',
			listUrl: '/collection-toy/list',
		});
	}

	// 3. Initialize the Collection Wizard
	CollectionWizard.init();
});

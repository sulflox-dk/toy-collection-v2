/**
 * EntityManager - Hybrid (JSON + HTML)
 * * Modes:
 * 1. 'json' (Default): Fetches JSON data, loops through it, and renders cards/rows using JS.
 * 2. 'html': Fetches a pre-rendered HTML partial (table/grid) from the server and injects it.
 */
class EntityManager {
	/**
	 * @param {string} entityName - e.g. 'manufacturer'
	 * @param {Object} config
	 * @param {string} [config.mode='json'] - 'json' or 'html'
	 * @param {string} [config.endpoint] - Base API URL (e.g. '/manufacturer')
	 * @param {string} [config.listUrl] - URL to fetch the list (defaults to endpoint)
	 * @param {Object} [config.ui] - UI Selectors
	 * @param {Function} [config.onRenderRow] - Callback for JSON rendering
	 */
	constructor(entityName, config = {}) {
		this.entityName = entityName;
		this.mode = config.mode || 'json'; // 'json' OR 'html'

		// API Endpoints — prepend SITE_URL so paths work in subdirectories
		const base = (typeof SITE_URL !== 'undefined' ? SITE_URL : '').replace(
			/\/+$/,
			'',
		);
		this.endpoint = base + (config.endpoint || `/${entityName}`);
		this.listUrl =
			base + (config.listUrl || config.endpoint || `/${entityName}`);

		this.ui = {
			grid: `#${entityName}-grid`,
			modal: `#${entityName}-modal`,
			form: `#${entityName}-form`,
			addBtn: `#btn-add-${entityName}`,
			searchInput: '#search-input',
			filterInputs: '.data-filter',
			resetBtn: '#btn-reset-filters',
			...config.ui,
		};

		this.onRenderRow = config.onRenderRow || this.defaultRenderRow;
		this.modalInstance = null;
		this.data = [];
		this.currentParams = { page: 1 };

		this.init();
	}

	init() {
		// Setup Add Button
		const addBtn = document.querySelector(this.ui.addBtn);
		if (addBtn) addBtn.addEventListener('click', () => this.openModal());

		// Setup Form
		const form = document.querySelector(this.ui.form);
		if (form) {
			form.addEventListener('submit', (e) => {
				e.preventDefault();
				// Check which button was clicked using e.submitter
				const action = e.submitter?.dataset?.action || 'save-close';
				const keepOpen = action === 'add-another';

				this.save(keepOpen); // Pass the flag to the save method
			});
		}

		// Setup Bootstrap Modal
		const modalEl = document.querySelector(this.ui.modal);
		if (modalEl && window.bootstrap) {
			this.modalInstance = new bootstrap.Modal(modalEl);
		}

		// Setup Search with Debounce ---
		const searchInput = document.querySelector(this.ui.searchInput);
		if (searchInput) {
			searchInput.addEventListener(
				'input',
				UiHelper.debounce((e) => {
					this.currentParams.q = e.target.value;
					this.currentParams.page = 1; // Always reset to page 1 on new search
					this.loadList(this.currentParams);
				}, 300),
			); // Waits 300ms after you stop typing to search
		}

		// Setup Dropdown Filters ---
		document.querySelectorAll(this.ui.filterInputs).forEach((filter) => {
			filter.addEventListener('change', (e) => {
				this.currentParams[e.target.name] = e.target.value;
				this.currentParams.page = 1; // Always reset to page 1 on new filter
				this.loadList(this.currentParams);
			});
		});

		// Setup Reset Button ---
		const resetBtn = document.querySelector(this.ui.resetBtn);
		if (resetBtn) {
			resetBtn.addEventListener('click', () => {
				if (searchInput) searchInput.value = '';
				document
					.querySelectorAll(this.ui.filterInputs)
					.forEach((f) => (f.value = ''));
				this.currentParams = { page: 1 }; // Wipe state back to default
				this.loadList(this.currentParams);
			});
		}

		// Initial Load
		this.loadList();
	}

	/**
	 * Master Load Method
	 * Dispatches to the correct loader based on mode
	 */
	loadList(params = {}) {
		if (this.mode === 'html') {
			this.loadHtml(params);
		} else {
			this.loadJson(params);
		}
	}

	/**
	 * STRATEGY 1: HTML Loader (Server-Side View)
	 * Fetches a partial HTML string and injects it.
	 */
	async loadHtml(params = {}) {
		const container = document.querySelector(this.ui.grid);
		if (!container) return;

		container.innerHTML = `<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>`;

		try {
			// FIX: Build the URL with query params manually first
			const fullUrl = ApiClient.buildUrl(this.listUrl, params);

			const html = await ApiClient.request(fullUrl, {
				method: 'GET',
				// We don't pass 'data' here anymore, because it's in the URL now
				headers: { Accept: 'text/html' },
			});

			container.innerHTML = html;
			this.attachRowListeners(container);
		} catch (error) {
			console.error('HTML Load Error:', error);
			container.innerHTML = `<div class="alert alert-danger">Failed to load data.</div>`;
		}
	}

	/**
	 * STRATEGY 2: JSON Loader (Client-Side View)
	 * Fetches JSON array and builds DOM using onRenderRow
	 */
	async loadJson(params = {}) {
		const container = document.querySelector(this.ui.grid);
		if (!container) return;

		container.innerHTML = `<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>`;

		try {
			// FIX: Use ApiClient.get() which DOES handle params automatically
			// Alternatively: ApiClient.request(ApiClient.buildUrl(this.listUrl, params), ...)
			const response = await ApiClient.get(this.listUrl, params);

			// Normalize response
			this.data = Array.isArray(response) ? response : response.data || [];

			if (this.data.length === 0) {
				container.innerHTML = `<div class="col-12 text-center py-5 text-muted">No records found.</div>`;
				return;
			}

			container.innerHTML = this.data
				.map((item) => this.onRenderRow(item))
				.join('');
			this.attachRowListeners(container);
		} catch (error) {
			console.error('JSON Load Error:', error);
			UiHelper.showToast('Failed to load data', 'error');
		}
	}

	/**
	 * Smart Save Method
	 * Handles Create (Reload List) and Update (Swap Row)
	 * @param {boolean} keepOpen - If true, clears the form and leaves the modal open after save
	 */
	async save(keepOpen = false) {
		const form = document.querySelector(this.ui.form);
		if (!form) return;

		// 1. Clear previous validation errors (remove red borders)
		form.querySelectorAll('.is-invalid').forEach((el) => {
			el.classList.remove('is-invalid');
		});

		if (!form.checkValidity()) {
			form.reportValidity();
			return;
		}

		const formData = new FormData(form);
		const id = formData.get('id');

		let url = this.endpoint;
		if (id) {
			url += `/${id}`;
			formData.append('_method', 'PUT');
		}

		try {
			// We expect the server to return JSON.
			// If it's an update, we want the new HTML row in the response!
			const response = await ApiClient.request(url, {
				method: 'POST',
				body: formData,
			});

			UiHelper.showToast('Saved successfully!', 'success');

			// --- NEW: Keep Open Logic ---
			if (keepOpen) {
				// User clicked "Save & Add Another"
				form.reset(); // Clear form fields

				// Explicitly clear the hidden ID, just to be safe
				const idInput = form.querySelector('[name="id"]');
				if (idInput) idInput.value = '';

				// Focus the first text input so they can keep typing immediately
				const firstInput = form.querySelector('input[type="text"]');
				if (firstInput) firstInput.focus();
			} else {
				// User clicked standard "Save" or "Create"
				this.closeModal();
			}
			// ----------------------------

			// SMART UPDATE LOGIC 🧠
			if (id && response.row_html && this.mode === 'html') {
				// If we got HTML back, find the row and swap it
				const existingRow = document.querySelector(
					`${this.ui.grid} tr[data-id="${id}"]`,
				);
				if (existingRow) {
					existingRow.outerHTML = response.row_html;
					// Re-attach listeners to the new row
					this.attachRowListeners(document.querySelector(this.ui.grid));
					// Highlight the row briefly so user sees the change
					const newRow = document.querySelector(
						`${this.ui.grid} tr[data-id="${id}"]`,
					);
					if (newRow) newRow.classList.add('table-success'); // Bootstrap color
					setTimeout(
						() => newRow?.classList.remove('table-success'),
						1500,
					);
					return; // Done! No reload needed.
				}
			}

			// Fallback: If it's a new item OR we didn't get HTML back, reload the list
			this.loadList();
		} catch (error) {
			console.error('Save Error:', error);

			// 🆕 FIELD-LEVEL ERROR HIGHLIGHTING
			if (error.field) {
				const input = form.querySelector(`[name="${error.field}"]`);
				if (input) {
					input.classList.add('is-invalid'); // Paint it red

					// Look for the feedback div to show the exact message
					const feedback =
						input.parentElement.querySelector('.invalid-feedback');
					if (feedback) {
						feedback.textContent = error.message;
					}

					// Focus the field so the user can fix it immediately
					input.focus();
				}
			}

			UiHelper.showToast(error.message || 'Failed to save.', 'error');
		}
	}

	/**
	 * Deletes item -> First triggers standard confirmation
	 */
	async delete(id) {
		const confirmed = await UiHelper.confirmDelete('this item');
		if (!confirmed) return;
		await this.executeDelete(id);
	}

	/**
	 * Executes the API call and handles Migration Requirements
	 */
	async executeDelete(id, migrateToId = null) {
		let url = `${this.endpoint}/${id}`;
		if (migrateToId) {
			url = ApiClient.buildUrl(url, { migrate_to: migrateToId });
		}

		try {
			await ApiClient.request(url, { method: 'DELETE' });

			UiHelper.showToast('Deleted successfully!', 'success');

			// Close migration modal if open
			const migrationModalEl = document.getElementById(
				'core-migration-modal',
			);
			if (migrationModalEl) {
				const bsModal = bootstrap.Modal.getInstance(migrationModalEl);
				if (bsModal) bsModal.hide();
			}

			this.loadList();
		} catch (error) {
			// 🚨 INTERCEPT 409 MIGRATION REQUESTS 🚨
			if (
				error.status === 409 &&
				error.data &&
				error.data.requires_migration
			) {
				this.showMigrationModal(id, error.data);
			} else {
				UiHelper.showToast(error.message || 'Failed to delete.', 'error');
			}
		}
	}

	/**
	 * Populates and shows the pre-existing Migration Modal
	 */
	async showMigrationModal(id, data) {
		try {
			const baseUrl = (
				typeof SITE_URL !== 'undefined' ? SITE_URL : ''
			).replace(/\/+$/, '');
			const optionsUrl = baseUrl + '/' + data.options_url;

			// Fetch dropdown options
			const options = await ApiClient.get(optionsUrl);

			const modalEl = document.getElementById('core-migration-modal');
			if (!modalEl) {
				console.error('Migration modal HTML not found in the DOM.');
				return;
			}

			// Elements
			const select = document.getElementById('core-migration-select');
			const messageEl = document.getElementById('core-migration-message');
			let btn = document.getElementById('core-migration-btn');

			// Reset UI state
			select.classList.remove('is-invalid');
			messageEl.textContent = data.message;

			// Build Options
			let optionsHtml = '<option value="">-- Select a new owner --</option>';
			options.forEach((opt) => {
				// Prevent XSS inside the dropdown
				const safeName = (opt.name || '')
					.replace(/</g, '&lt;')
					.replace(/>/g, '&gt;');
				optionsHtml += `<option value="${opt.id}">${safeName}</option>`;
			});
			select.innerHTML = optionsHtml;

			// Replace button with a clone to wipe any old event listeners
			const newBtn = btn.cloneNode(true);
			btn.parentNode.replaceChild(newBtn, btn);

			newBtn.innerHTML = 'Migrate and Delete';
			newBtn.disabled = false;

			newBtn.addEventListener('click', () => {
				const targetId = select.value;
				if (!targetId) {
					select.classList.add('is-invalid');
					return;
				}

				select.classList.remove('is-invalid');
				newBtn.disabled = true;
				newBtn.innerHTML =
					'<i class="fa-solid fa-spinner fa-spin"></i> Processing...';

				this.executeDelete(id, targetId);
			});

			// Show Modal
			const bsModal = new bootstrap.Modal(modalEl);
			bsModal.show();
		} catch (error) {
			console.error('Migration Setup Error:', error);
			UiHelper.showToast('Failed to load migration options.', 'error');
		}
	}

	/**
	 * Attach Event Listeners to the dynamic Grid/Table
	 * Works for both JSON and HTML modes!
	 */
	attachRowListeners(container) {
		// Edit Buttons
		container.querySelectorAll('.btn-edit').forEach((btn) => {
			btn.addEventListener('click', (e) => {
				const id = e.currentTarget.dataset.id;

				// If HTML mode: We look for data-json attribute on the button
				// If JSON mode: We look in this.data array
				let itemData = null;

				if (this.mode === 'html' && e.currentTarget.dataset.json) {
					try {
						itemData = JSON.parse(e.currentTarget.dataset.json);
					} catch (e) {
						console.error('Bad JSON in data-attribute');
					}
				} else {
					itemData = this.data.find((d) => d.id == id);
				}

				if (itemData) this.openModal(itemData);
			});
		});

		// Delete Buttons
		container.querySelectorAll('.btn-delete').forEach((btn) => {
			btn.addEventListener('click', (e) => {
				this.delete(e.currentTarget.dataset.id);
			});
		});

		// PAGINATION LISTENERS
		container.querySelectorAll('.page-link').forEach((link) => {
			link.addEventListener('click', (e) => {
				e.preventDefault(); // Stop page jump

				const page = e.currentTarget.dataset.page;
				if (page && page > 0) {
					this.currentParams.page = page; // Update current state
					this.loadList(this.currentParams); // Load with full state
				}
			});
		});
	}

	openModal(data = null) {
		const form = document.querySelector(this.ui.form);
		if (!form) return;

		form.reset();

		const idInput = form.querySelector('[name="id"]');
		if (idInput) idInput.value = '';

		// --- Dynamic Title & Button Logic ---
		const titleEl = document.querySelector(`${this.ui.modal} .modal-title`);
		const submitBtn = form.querySelector('button[data-action="save-close"]'); // Find the submit button
		const addAnotherBtn = form.querySelector(
			'button[data-action="add-another"]',
		);

		if (titleEl) {
			// Read the current text, strip out "Edit " or "Add ", and keep the base word
			const baseName = titleEl.textContent
				.replace(/(Edit|Add)\s+/i, '')
				.trim();

			if (data) {
				// EDIT MODE
				titleEl.innerHTML = `<i class="fa-solid fa-pencil me-2 text-primary"></i> Edit ${baseName}`;
				if (submitBtn) {
					submitBtn.innerHTML = `Save`;
				}
				if (addAnotherBtn) addAnotherBtn.classList.add('d-none');
			} else {
				// CREATE MODE
				titleEl.innerHTML = `<i class="fa-solid fa-plus me-2 text-primary"></i> Add ${baseName}`;
				if (submitBtn) {
					submitBtn.innerHTML = `Save`;
				}
				if (addAnotherBtn) addAnotherBtn.classList.remove('d-none');
			}
		}
		// ------------------------------------

		if (data) this.populateForm(form, data);

		if (this.modalInstance) this.modalInstance.show();
	}

	closeModal() {
		if (this.modalInstance) this.modalInstance.hide();
	}

	populateForm(form, data) {
		Object.keys(data).forEach((key) => {
			const input = form.querySelector(`[name="${key}"]`);
			if (input) {
				if (input.type === 'checkbox') input.checked = !!data[key];
				else input.value = data[key];
			}
		});
	}

	defaultRenderRow(item) {
		return `<div class="col">Card for ${item.id}</div>`;
	}
}

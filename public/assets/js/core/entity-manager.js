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
		this.mode = config.mode || 'json';

		// Track active requests to prevent race conditions 🏁
		this.activeAbortController = null;

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
		// 1. Read existing state from URL 🗺️
		const urlParams = new URLSearchParams(window.location.search);
		urlParams.forEach((value, key) => {
			this.currentParams[key] = value;
		});

		// 2. Setup UI Elements & Sync with URL State
		const addBtn = document.querySelector(this.ui.addBtn);
		if (addBtn) addBtn.addEventListener('click', () => this.openModal());

		const form = document.querySelector(this.ui.form);
		if (form) {
			form.addEventListener('submit', (e) => {
				e.preventDefault();
				const action = e.submitter?.dataset?.action || 'save-close';
				const keepOpen = action === 'add-another';
				this.save(keepOpen);
			});
		}

		const modalEl = document.querySelector(this.ui.modal);
		if (modalEl && window.bootstrap) {
			this.modalInstance = new bootstrap.Modal(modalEl);
		}

		// Search Input Sync & Listener
		const searchInput = document.querySelector(this.ui.searchInput);
		if (searchInput) {
			// Sync UI to URL param 'q'
			if (this.currentParams.q) searchInput.value = this.currentParams.q;

			searchInput.addEventListener(
				'input',
				UiHelper.debounce((e) => {
					this.currentParams.q = e.target.value;
					this.currentParams.page = 1;
					this.loadList(this.currentParams);
				}, 300),
			);
		}

		// Dropdown Filters Sync & Listeners
		document.querySelectorAll(this.ui.filterInputs).forEach((filter) => {
			// Sync UI to URL param matching the input name
			if (this.currentParams[filter.name]) {
				filter.value = this.currentParams[filter.name];
			}

			filter.addEventListener('change', (e) => {
				this.currentParams[e.target.name] = e.target.value;
				this.currentParams.page = 1;
				this.loadList(this.currentParams);
			});
		});

		// Reset Button
		const resetBtn = document.querySelector(this.ui.resetBtn);
		if (resetBtn) {
			resetBtn.addEventListener('click', () => {
				if (searchInput) searchInput.value = '';
				document
					.querySelectorAll(this.ui.filterInputs)
					.forEach((f) => (f.value = ''));
				this.currentParams = { page: 1 };
				this.loadList(this.currentParams);
			});
		}

		// 3. Initial Load (Uses currentParams from URL if they exist)
		this.loadList(this.currentParams);
	}

	/**
	 * Master Load Method
	 * Dispatches to the correct loader based on mode with race condition protection.
	 */
	async loadList(params = {}) {
		// Cancel any existing request that is still in flight 🛑
		if (this.activeAbortController) {
			this.activeAbortController.abort();
		}

		// Create a new controller for this specific request
		this.activeAbortController = new AbortController();
		const signal = this.activeAbortController.signal;

		const container = document.querySelector(this.ui.grid);
		if (container) container.style.opacity = '0.5';

		this.updateUrl(params);

		try {
			if (this.mode === 'html') {
				await this.loadHtml(params, signal);
			} else {
				await this.loadJson(params, signal);
			}
		} catch (error) {
			if (error.name === 'AbortError') {
				return; // Silently ignore cancelled requests
			}
			console.error('Load Error:', error);
			UiHelper.showToast('Failed to load data', 'error');
		} finally {
			// Restore opacity only if this is still the most recent request
			if (this.activeAbortController?.signal === signal) {
				if (container) container.style.opacity = '1';
				this.activeAbortController = null;
			}
		}
	}

	/**
	 * STRATEGY 1: HTML Loader (Server-Side View)
	 */
	async loadHtml(params = {}, signal) {
		const container = document.querySelector(this.ui.grid);
		if (!container) return;

		const fullUrl = ApiClient.buildUrl(this.listUrl, params);

		const html = await ApiClient.request(fullUrl, {
			method: 'GET',
			headers: { Accept: 'text/html' },
			signal: signal,
		});

		container.innerHTML = html;
		this.attachRowListeners(container);
	}

	/**
	 * STRATEGY 2: JSON Loader (Client-Side View)
	 */
	async loadJson(params = {}, signal) {
		const container = document.querySelector(this.ui.grid);
		if (!container) return;

		const response = await ApiClient.request(
			ApiClient.buildUrl(this.listUrl, params),
			{
				method: 'GET',
				signal: signal,
			},
		);

		this.data = Array.isArray(response) ? response : response.data || [];

		if (this.data.length === 0) {
			container.innerHTML = `<div class="col-12 text-center py-5 text-muted">No records found.</div>`;
			return;
		}

		container.innerHTML = this.data
			.map((item) => this.onRenderRow(item))
			.join('');
		this.attachRowListeners(container);
	}

	/**
	 * Smart Save Method
	 * Handles Create (Reload List) and Update (Safe Swap)
	 * @param {boolean} keepOpen - If true, clears the form and leaves the modal open
	 */
	async save(keepOpen = false) {
		const form = document.querySelector(this.ui.form);
		if (!form) return;

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
			const response = await ApiClient.request(url, {
				method: 'POST',
				body: formData,
			});

			UiHelper.showToast('Saved successfully!', 'success');

			if (keepOpen) {
				form.reset();
				const idInput = form.querySelector('[name="id"]');
				if (idInput) idInput.value = '';

				const firstInput = form.querySelector('input[type="text"]');
				if (firstInput) firstInput.focus();
			} else {
				this.closeModal();
			}

			// SMART UPDATE LOGIC 🧠 (Secure Swap)
			if (id && response.row_html && this.mode === 'html') {
				const existingRow = document.querySelector(
					`${this.ui.grid} [data-id="${id}"]`,
				);

				if (existingRow) {
					const newRow = UiHelper.safeSwap(existingRow, response.row_html);

					if (newRow) {
						this.attachRowListeners(document.querySelector(this.ui.grid));
						newRow.classList.add('table-success');
						setTimeout(() => {
							if (newRow.isConnected)
								newRow.classList.remove('table-success');
						}, 1500);

						return;
					}
				}
			}

			this.loadList();
		} catch (error) {
			console.error('Save Error:', error);

			if (error.field) {
				const input = form.querySelector(`[name="${error.field}"]`);
				if (input) {
					input.classList.add('is-invalid');
					const feedback =
						input.parentElement.querySelector('.invalid-feedback');
					if (feedback) feedback.textContent = error.message;
					input.focus();
				}
			}

			UiHelper.showToast(error.message || 'Failed to save.', 'error');
		}
	}

	async delete(id) {
		const confirmed = await UiHelper.confirmDelete('this item');
		if (!confirmed) return;
		await this.executeDelete(id);
	}

	async executeDelete(id, migrateToId = null) {
		let url = `${this.endpoint}/${id}`;
		if (migrateToId) {
			url = ApiClient.buildUrl(url, { migrate_to: migrateToId });
		}

		try {
			await ApiClient.request(url, { method: 'DELETE' });
			UiHelper.showToast('Deleted successfully!', 'success');

			const migrationModalEl = document.getElementById(
				'core-migration-modal',
			);
			if (migrationModalEl) {
				const bsModal = bootstrap.Modal.getInstance(migrationModalEl);
				if (bsModal) bsModal.hide();
			}

			this.loadList();
		} catch (error) {
			if (error.status === 409 && error.data?.requires_migration) {
				this.showMigrationModal(id, error.data);
			} else {
				UiHelper.showToast(error.message || 'Failed to delete.', 'error');
			}
		}
	}

	async showMigrationModal(id, data) {
		try {
			const baseUrl = (
				typeof SITE_URL !== 'undefined' ? SITE_URL : ''
			).replace(/\/+$/, '');
			const optionsUrl = baseUrl + '/' + data.options_url;

			const options = await ApiClient.get(optionsUrl);
			const modalEl = document.getElementById('core-migration-modal');
			if (!modalEl) return;

			const select = document.getElementById('core-migration-select');
			const messageEl = document.getElementById('core-migration-message');
			let btn = document.getElementById('core-migration-btn');

			select.classList.remove('is-invalid');
			messageEl.textContent = data.message;

			let optionsHtml = '<option value="">-- Select a new owner --</option>';
			options.forEach((opt) => {
				const safeName = (opt.name || '')
					.replace(/</g, '&lt;')
					.replace(/>/g, '&gt;');
				optionsHtml += `<option value="${opt.id}">${safeName}</option>`;
			});
			select.innerHTML = optionsHtml;

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

			const bsModal = new bootstrap.Modal(modalEl);
			bsModal.show();
		} catch (error) {
			UiHelper.showToast('Failed to load migration options.', 'error');
		}
	}

	attachRowListeners(container) {
		container.querySelectorAll('.btn-edit').forEach((btn) => {
			btn.addEventListener('click', (e) => {
				const id = e.currentTarget.dataset.id;
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

		container.querySelectorAll('.btn-delete').forEach((btn) => {
			btn.addEventListener('click', (e) => {
				this.delete(e.currentTarget.dataset.id);
			});
		});

		container.querySelectorAll('.page-link').forEach((link) => {
			link.addEventListener('click', (e) => {
				e.preventDefault();
				const page = e.currentTarget.dataset.page;
				if (page && page > 0) {
					this.currentParams.page = page;
					this.loadList(this.currentParams);
				}
			});
		});
	}

	openModal(data = null) {
		const form = document.querySelector(this.ui.form);
		if (!form) return;

		form.reset();

		const idInput = form.querySelector('[name=\"id\"]');
		if (idInput) idInput.value = '';

		const titleEl = document.querySelector(`${this.ui.modal} .modal-title`);
		const submitBtn = form.querySelector(
			'button[data-action=\"save-close\"]',
		);
		const addAnotherBtn = form.querySelector(
			'button[data-action=\"add-another\"]',
		);

		if (titleEl) {
			const baseName = titleEl.textContent
				.replace(/(Edit|Add)\s+/i, '')
				.trim();

			if (data) {
				titleEl.innerHTML = `<i class=\"fa-solid fa-pencil me-2 text-primary\"></i> Edit ${baseName}`;
				if (submitBtn) submitBtn.innerHTML = `Save`;
				if (addAnotherBtn) addAnotherBtn.classList.add('d-none');
			} else {
				titleEl.innerHTML = `<i class=\"fa-solid fa-plus me-2 text-primary\"></i> Add ${baseName}`;
				if (submitBtn) submitBtn.innerHTML = `Save`;
				if (addAnotherBtn) addAnotherBtn.classList.remove('d-none');
			}
		}

		if (data) this.populateForm(form, data);
		if (this.modalInstance) this.modalInstance.show();
	}

	closeModal() {
		if (this.modalInstance) this.modalInstance.hide();
	}

	populateForm(form, data) {
		Object.keys(data).forEach((key) => {
			const input = form.querySelector(`[name=\"${key}\"]`);
			if (input) {
				if (input.type === 'checkbox') input.checked = !!data[key];
				else input.value = data[key];
			}
		});
	}

	defaultRenderRow(item) {
		return `<div class=\"col\">Card for ${item.id}</div>`;
	}

	// Add this method to the EntityManager class
	updateUrl(params) {
		const url = new URL(window.location);
		// Clear existing params to avoid duplicates
		url.search = '';

		Object.keys(params).forEach((key) => {
			if (params[key]) {
				url.searchParams.set(key, params[key]);
			}
		});

		window.history.pushState({}, '', url);
	}
}

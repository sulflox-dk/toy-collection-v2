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
		const base = (typeof SITE_URL !== 'undefined' ? SITE_URL : '').replace(/\/+$/, '');
		this.endpoint = base + (config.endpoint || `/${entityName}`);
		this.listUrl = base + (config.listUrl || config.endpoint || `/${entityName}`);

		this.ui = {
			grid: `#${entityName}-grid`,
			modal: `#${entityName}-modal`,
			form: `#${entityName}-form`,
			addBtn: `#btn-add-${entityName}`,
			searchInput: '#search-input',
			...config.ui,
		};

		this.onRenderRow = config.onRenderRow || this.defaultRenderRow;
		this.modalInstance = null;
		this.data = [];

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
				this.save();
			});
		}

		// Setup Bootstrap Modal
		const modalEl = document.querySelector(this.ui.modal);
		if (modalEl && window.bootstrap) {
			this.modalInstance = new bootstrap.Modal(modalEl);
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
	async loadHtml(params) {
		const container = document.querySelector(this.ui.grid);
		if (!container) return;

		// Loading State
		container.innerHTML = `<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>`;

		try {
			// We expect the server to return pure HTML here
			const html = await ApiClient.request(this.listUrl, {
				method: 'GET',
				data: params,
				headers: { Accept: 'text/html' }, // Good practice to tell server what we want
			});

			// Inject
			container.innerHTML = html;

			// Re-attach listeners because the DOM buttons are new
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
	async loadJson(params) {
		const container = document.querySelector(this.ui.grid);
		if (!container) return;

		// Loading State
		container.innerHTML = `<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>`;

		try {
			const response = await ApiClient.request(this.listUrl, {
				method: 'GET',
				data: params,
			});

			// Normalize response
			this.data = Array.isArray(response) ? response : response.data || [];

			if (this.data.length === 0) {
				container.innerHTML = `<div class="col-12 text-center py-5 text-muted">No records found.</div>`;
				return;
			}

			// Render Client-Side
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
	 * Saves data (POST/PUT) -> Always JSON
	 */
	async save() {
		const form = document.querySelector(this.ui.form);
		if (!form || !form.checkValidity()) {
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
			await ApiClient.request(url, {
				method: 'POST',
				body: formData,
			});

			this.closeModal();
			UiHelper.showToast('Saved successfully!', 'success');

			// Reload the list (using whatever mode is active)
			this.loadList();
		} catch (error) {
			console.error('Save Error:', error);
			UiHelper.showToast('Failed to save.', 'error');
		}
	}

	/**
	 * Deletes item -> Always JSON API
	 */
	async delete(id) {
		if (!confirm('Are you sure?')) return;

		try {
			await ApiClient.request(`${this.endpoint}/${id}`, {
				method: 'DELETE',
			});

			UiHelper.showToast('Item deleted.', 'success');
			this.loadList();
		} catch (error) {
			UiHelper.showToast('Failed to delete.', 'error');
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
	}

	openModal(data = null) {
		const form = document.querySelector(this.ui.form);
		if (!form) return;

		form.reset();
		form.querySelector('[name="id"]').value = '';

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

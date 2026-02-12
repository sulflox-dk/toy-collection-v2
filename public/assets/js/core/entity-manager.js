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
	 */
	async save() {
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

			this.closeModal();
			UiHelper.showToast('Saved successfully!', 'success');

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

		// PAGINATION LISTENERS 📄
		container.querySelectorAll('.page-link').forEach((link) => {
			link.addEventListener('click', (e) => {
				e.preventDefault(); // Stop page jump

				const page = e.currentTarget.dataset.page;
				// Only load if page is valid (not disabled)
				if (page && page > 0) {
					this.loadList({ page: page });
				}
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

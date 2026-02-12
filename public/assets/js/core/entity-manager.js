/**
 * EntityManager - Handles CRUD operations for V2 Architecture
 * Works with the RESTful Router and Config system
 */
class EntityManager {
	/**
	 * @param {string} entityName - e.g., 'manufacturer'
	 * @param {Object} config - Configuration object
	 * @param {string} [config.endpoint] - API Endpoint (default: /entityName)
	 * @param {Object} [config.ui] - UI Selectors override
	 * @param {Function} [config.onRenderRow] - Function to render a single row/card
	 */
	constructor(entityName, config = {}) {
		this.entityName = entityName;

		// V2 Routing: Default to "/manufacturer" if not specified
		this.endpoint = config.endpoint || `/${entityName}`;

		// UI Selectors with defaults
		this.ui = {
			grid: `#${entityName}-grid`, // Container for the list
			modal: `#${entityName}-modal`, // The Add/Edit Modal
			form: `#${entityName}-form`, // The Form inside the modal
			addBtn: `#btn-add-${entityName}`, // Button to open modal
			saveBtn: `#btn-save-${entityName}`, // Save button in modal
			searchInput: '#search-input', // Optional search box
			...config.ui,
		};

		// Callbacks
		this.onRenderRow = config.onRenderRow || this.defaultRenderRow;

		// Internal state
		this.data = [];
		this.modalInstance = null;

		this.init();
	}

	/**
	 * Initialize Event Listeners
	 */
	init() {
		// 1. Setup Add Button
		const addBtn = document.querySelector(this.ui.addBtn);
		if (addBtn) {
			addBtn.addEventListener('click', () => this.openModal());
		}

		// 2. Setup Form Submission
		const form = document.querySelector(this.ui.form);
		if (form) {
			form.addEventListener('submit', (e) => {
				e.preventDefault();
				this.save();
			});
		}

		// 3. Setup Bootstrap Modal
		const modalEl = document.querySelector(this.ui.modal);
		if (modalEl && window.bootstrap) {
			this.modalInstance = new bootstrap.Modal(modalEl);
		}

		// 4. Initial Load
		this.loadData();
	}

	/**
	 * Load data from the API
	 * GET /manufacturer
	 */
	async loadData(params = {}) {
		try {
			// Use your ApiClient to fetch
			const response = await ApiClient.request(this.endpoint, {
				method: 'GET',
				data: params,
			});

			// Handle wrapped response (e.g. { data: [...] } vs [...])
			this.data = Array.isArray(response) ? response : response.data || [];

			this.render();
		} catch (error) {
			console.error('Load Error:', error);
			UiHelper.showToast('Failed to load data.', 'error');
		}
	}

	/**
	 * Render the list (Grid or Table)
	 */
	render() {
		const container = document.querySelector(this.ui.grid);
		if (!container) return;

		if (this.data.length === 0) {
			container.innerHTML = `
                <div class="col-12 text-center py-5 text-muted">
                    <i class="fa-solid fa-box-open fa-3x mb-3"></i>
                    <p>No ${this.entityName}s found.</p>
                </div>`;
			return;
		}

		// Map data to HTML using the callback
		container.innerHTML = this.data
			.map((item) => this.onRenderRow(item))
			.join('');

		// Re-attach delete/edit listeners to the new dynamic HTML
		this.attachRowListeners(container);
	}

	/**
	 * Create or Update an entity
	 */
	async save() {
		const form = document.querySelector(this.ui.form);
		if (!form) return;

		// 1. Validate
		if (!form.checkValidity()) {
			form.reportValidity();
			return;
		}

		// 2. Prepare Data
		const formData = new FormData(form);
		const id = formData.get('id'); // Hidden input for ID

		// 3. Determine URL and Method
		let url = this.endpoint;
		let method = 'POST';

		if (id) {
			// Update: PUT /manufacturer/123
			url = `${this.endpoint}/${id}`;
			// Standard trick: PHP often handles PUT better if sent as POST with _method override
			// especially if files are involved.
			formData.append('_method', 'PUT');
		}

		// 4. Send Request
		try {
			await ApiClient.request(url, {
				method: method,
				body: formData,
			});

			this.closeModal();
			UiHelper.showToast(
				`${this.entityName} saved successfully!`,
				'success',
			);
			this.loadData(); // Refresh grid
		} catch (error) {
			console.error('Save Error:', error);

			if (error.status === 422) {
				// Validation Error from API
				UiHelper.showToast('Please check the form for errors.', 'warning');
				// You could add logic here to highlight specific fields based on error.data
			} else {
				UiHelper.showToast('Failed to save. Please try again.', 'error');
			}
		}
	}

	/**
	 * Delete an entity
	 * DELETE /manufacturer/123
	 */
	async delete(id) {
		if (
			!confirm(
				'Are you sure you want to delete this item? This cannot be undone.',
			)
		) {
			return;
		}

		try {
			await ApiClient.request(`${this.endpoint}/${id}`, {
				method: 'DELETE',
			});

			UiHelper.showToast('Item deleted.', 'success');
			this.loadData();
		} catch (error) {
			console.error('Delete Error:', error);
			UiHelper.showToast('Failed to delete item.', 'error');
		}
	}

	/**
	 * Open the modal for Creating (empty) or Editing (populated)
	 */
	openModal(data = null) {
		const form = document.querySelector(this.ui.form);
		if (!form) return;

		form.reset();

		// Remove old hidden ID if it exists
		const idInput = form.querySelector('input[name="id"]');
		if (idInput) idInput.value = '';

		if (data) {
			// Editing: Populate form fields
			this.populateForm(form, data);

			// Update Modal Title if exists
			const title = document.querySelector(`${this.ui.modal} .modal-title`);
			if (title) title.textContent = `Edit ${this.entityName}`;
		} else {
			// Creating
			const title = document.querySelector(`${this.ui.modal} .modal-title`);
			if (title) title.textContent = `Add New ${this.entityName}`;
		}

		if (this.modalInstance) {
			this.modalInstance.show();
		}
	}

	closeModal() {
		if (this.modalInstance) {
			this.modalInstance.hide();
		}
	}

	/**
	 * Helper to fill form inputs with JSON data
	 */
	populateForm(form, data) {
		Object.keys(data).forEach((key) => {
			const input = form.querySelector(`[name="${key}"]`);
			if (input) {
				if (input.type === 'checkbox') {
					input.checked = !!data[key];
				} else {
					input.value = data[key];
				}
			}
		});
	}

	/**
	 * Listen for clicks on the generated grid (Edit/Delete buttons)
	 */
	attachRowListeners(container) {
		// Edit Buttons
		container.querySelectorAll('.btn-edit').forEach((btn) => {
			btn.addEventListener('click', (e) => {
				const id = e.currentTarget.dataset.id;
				const item = this.data.find((d) => d.id == id);
				if (item) this.openModal(item);
			});
		});

		// Delete Buttons
		container.querySelectorAll('.btn-delete').forEach((btn) => {
			btn.addEventListener('click', (e) => {
				const id = e.currentTarget.dataset.id;
				this.delete(id);
			});
		});
	}

	/**
	 * Default fallback renderer if none provided
	 */
	defaultRenderRow(item) {
		return `
            <div class="col-12 mb-2">
                <div class="card p-3">
                    <strong>${item.name || 'Item ' + item.id}</strong>
                </div>
            </div>`;
	}
}

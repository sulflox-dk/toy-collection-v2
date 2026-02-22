/**
 * SearchableDropdown — Reusable searchable dropdown selector.
 *
 * Register a named configuration, then use generic HTML markup:
 *
 *   <div class="sd-wrapper" data-sd="subjects">
 *       <div onclick="SearchableDropdown.toggle(this)">
 *           <div class="sd-display-name">Select...</div>
 *           <div class="sd-display-meta" style="display:none;"></div>
 *       </div>
 *       <div class="sd-dropdown ...">
 *           <input class="sd-search" onkeyup="SearchableDropdown.filter(this)">
 *           <div class="sd-results"></div>
 *       </div>
 *   </div>
 *
 *   SearchableDropdown.register('subjects', {
 *       getItems:      () => [...],          // returns current valid items
 *       searchFields:  ['name', 'type'],     // fields to match against query
 *       displayName:   'name',               // field shown as primary text
 *       displayMeta:   'type',               // field shown as subtitle (optional)
 *       valueField:    'id',                 // field stored in the hidden input
 *       inputSelector: '.item-subject-id',   // hidden input (relative to rowSelector)
 *       rowSelector:   '.item-row',          // closest ancestor containing the input
 *       placeholder:   'Select Subject...',  // text when nothing selected
 *       emptyText:     'No results found.',  // text when search yields nothing
 *   });
 */
const SearchableDropdown = {
	configs: {},

	/**
	 * Register a named dropdown configuration.
	 */
	register(name, config) {
		this.configs[name] = Object.assign(
			{
				searchFields: ['name'],
				displayName: 'name',
				displayMeta: null,
				valueField: 'id',
				inputSelector: null,
				rowSelector: null,
				placeholder: 'Select...',
				emptyText: 'No results found.',
			},
			config,
		);
	},

	/**
	 * Toggle a dropdown open/closed. Call from the trigger's onclick.
	 */
	toggle(element) {
		const wrapper = element.closest('.sd-wrapper');
		const dropdown = wrapper.querySelector('.sd-dropdown');
		const input = dropdown.querySelector('.sd-search');
		const configName = wrapper.dataset.sd;

		// Close all other open dropdowns first
		document.querySelectorAll('.sd-dropdown').forEach((dd) => {
			if (dd !== dropdown) dd.classList.add('d-none');
		});

		dropdown.classList.toggle('d-none');
		if (!dropdown.classList.contains('d-none')) {
			input.value = '';
			input.focus();
			this._renderResults(wrapper, configName, '');
		}
	},

	/**
	 * Filter results as the user types. Call from the search input's onkeyup.
	 */
	filter(inputEl) {
		const wrapper = inputEl.closest('.sd-wrapper');
		const configName = wrapper.dataset.sd;
		this._renderResults(wrapper, configName, inputEl.value.toLowerCase());
	},

	/**
	 * Reset any selections whose value is no longer in the current item set.
	 * Call after the backing data changes (e.g. universe switch).
	 */
	validateSelections(name) {
		const config = this.configs[name];
		if (!config) return;

		const validItems = config.getItems();
		const validIds = new Set(
			validItems.map((item) => parseInt(item[config.valueField])),
		);

		document.querySelectorAll(`.sd-wrapper[data-sd="${name}"]`).forEach(
			(wrapper) => {
				const row = config.rowSelector
					? wrapper.closest(config.rowSelector)
					: wrapper;
				const input = row.querySelector(config.inputSelector);
				if (!input) return;

				if (input.value && !validIds.has(parseInt(input.value))) {
					input.value = '';
					wrapper.querySelector('.sd-display-name').textContent =
						config.placeholder;
					const meta = wrapper.querySelector('.sd-display-meta');
					if (meta) meta.style.display = 'none';
				}
			},
		);
	},

	// --- Internal ---

	_renderResults(wrapper, configName, query) {
		const config = this.configs[configName];
		if (!config) return;

		const resultsContainer = wrapper.querySelector('.sd-results');
		let items = config.getItems();

		if (query) {
			items = items.filter((item) =>
				config.searchFields.some((field) =>
					(item[field] || '').toLowerCase().includes(query),
				),
			);
		}

		resultsContainer.innerHTML = '';

		if (items.length === 0) {
			resultsContainer.innerHTML = `<div class="p-3 text-muted small text-center">${config.emptyText}</div>`;
			return;
		}

		items.forEach((item) => {
			const div = document.createElement('div');
			div.className = 'p-2 border-bottom sd-result-item bg-white';
			div.style.cursor = 'pointer';

			const nameEl = document.createElement('div');
			nameEl.className = 'fw-bold small text-dark';
			nameEl.textContent = item[config.displayName];
			div.appendChild(nameEl);

			if (config.displayMeta && item[config.displayMeta]) {
				const metaEl = document.createElement('div');
				metaEl.className = 'text-muted';
				metaEl.style.fontSize = '0.7rem';
				metaEl.textContent = item[config.displayMeta];
				div.appendChild(metaEl);
			}

			div.onclick = () => this._select(wrapper, config, item);
			resultsContainer.appendChild(div);
		});
	},

	_select(wrapper, config, item) {
		// Update hidden input
		if (config.inputSelector) {
			const row = config.rowSelector
				? wrapper.closest(config.rowSelector)
				: wrapper;
			const input = row.querySelector(config.inputSelector);
			if (input) input.value = item[config.valueField];
		}

		// Update display
		wrapper.querySelector('.sd-display-name').textContent =
			item[config.displayName];

		const meta = wrapper.querySelector('.sd-display-meta');
		if (meta && config.displayMeta) {
			meta.textContent = item[config.displayMeta] || '';
			meta.style.display = item[config.displayMeta] ? 'block' : 'none';
		}

		// Close dropdown
		wrapper.querySelector('.sd-dropdown').classList.add('d-none');
	},
};

// Close all searchable dropdowns when clicking outside
document.addEventListener('click', (e) => {
	if (!e.target.closest('.sd-wrapper')) {
		document
			.querySelectorAll('.sd-dropdown')
			.forEach((dd) => dd.classList.add('d-none'));
	}
});

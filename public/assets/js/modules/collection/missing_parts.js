// Missing Parts overview — deliberately NOT built on EntityManager.
// This page has no create/update/delete of its own (editing happens via the
// existing CollectionWizard modal), and EntityManager's default selectors
// (#search-input, .data-filter) would collide with the *other* EntityManager
// instance collection_toys.js also creates on this page for CollectionWizard
// support — so this is a small purpose-built loader instead.
document.addEventListener('DOMContentLoaded', () => {
	const grid = document.getElementById('missing-part-grid');
	if (!grid) return;

	const searchPart = document.getElementById('mp-search-part');
	const searchToy = document.getElementById('mp-search-toy');
	const filterUniverse = document.getElementById('mp-filter-universe');
	const filterManufacturer = document.getElementById('mp-filter-manufacturer');
	const filterToyLine = document.getElementById('mp-filter-toy-line');
	const filterProductType = document.getElementById('mp-filter-product-type');
	const sortSelect = document.getElementById('mp-sort');
	const showRepro = document.getElementById('mp-show-repro');
	const resetBtn = document.getElementById('mp-reset-filters');

	const baseUrl = typeof SITE_URL !== 'undefined' ? SITE_URL : '/';
	let currentPage = 1;

	function buildParams() {
		const params = new URLSearchParams();
		if (searchPart.value.trim()) params.set('part_q', searchPart.value.trim());
		if (searchToy.value.trim()) params.set('toy_q', searchToy.value.trim());
		if (filterUniverse.value) params.set('universe_id', filterUniverse.value);
		if (filterManufacturer.value) params.set('manufacturer_id', filterManufacturer.value);
		if (filterToyLine.value) params.set('toy_line_id', filterToyLine.value);
		if (filterProductType.value) params.set('product_type_id', filterProductType.value);
		if (sortSelect.value) params.set('sort', sortSelect.value);
		if (showRepro.checked) params.set('show_repro', '1');
		params.set('page', currentPage);
		return params;
	}

	async function loadList() {
		grid.innerHTML = '<div class="p-5 text-center"><i class="fa-solid fa-spinner fa-spin fa-2x text-muted"></i></div>';

		try {
			const response = await fetch(baseUrl + 'missing-part/list?' + buildParams().toString());
			if (!response.ok) throw new Error('Network response was not ok');
			grid.innerHTML = await response.text();

			// Wire up pagination links rendered inside the partial
			grid.querySelectorAll('[data-page]').forEach((el) => {
				el.addEventListener('click', (e) => {
					e.preventDefault();
					currentPage = parseInt(el.dataset.page, 10) || 1;
					loadList();
				});
			});
		} catch (error) {
			console.error('Failed to load missing parts', error);
			grid.innerHTML = '<div class="p-4 text-danger">Failed to load missing parts.</div>';
		}
	}

	[searchPart, searchToy].forEach((input) => {
		input.addEventListener(
			'input',
			UiHelper.debounce(() => {
				currentPage = 1;
				loadList();
			}, 300),
		);
	});

	[filterUniverse, filterManufacturer, filterToyLine, filterProductType, sortSelect, showRepro].forEach((el) => {
		el.addEventListener('change', () => {
			currentPage = 1;
			loadList();
		});
	});

	resetBtn.addEventListener('click', () => {
		searchPart.value = '';
		searchToy.value = '';
		filterUniverse.value = '';
		filterManufacturer.value = '';
		filterToyLine.value = '';
		filterProductType.value = '';
		sortSelect.value = 'part';
		showRepro.checked = false;
		currentPage = 1;
		loadList();
	});

	// Refresh the list after the wizard modal closes, in case a part's
	// present/repro status just changed.
	const modalEl = document.getElementById('entity-modal');
	if (modalEl) {
		modalEl.addEventListener('hidden.bs.modal', () => loadList());
	}

	loadList();
});

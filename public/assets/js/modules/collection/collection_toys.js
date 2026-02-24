document.addEventListener('DOMContentLoaded', () => {
	// 1. Handle View Mode (List vs Cards)
	const params = new URLSearchParams(window.location.search);
	let initialView = params.get('view');

	if (!initialView && typeof window.getCookie === 'function') {
		initialView = window.getCookie('collection-toy_view');
	}

	initialView = initialView || 'cards'; // Default to cards

	if (typeof window.setViewMode === 'function') {
		window.setViewMode(initialView, false);
	}

	// 2. Initialize the Entity Manager
	if (typeof EntityManager !== 'undefined') {
		// We assign it to window.collectionToyManager so we can reference it globally later
		window.collectionToyManager = new EntityManager('collection-toy', {
			mode: 'html',
			endpoint: '/collection-toy',
			listUrl: '/collection-toy/list',
		});
	}
});

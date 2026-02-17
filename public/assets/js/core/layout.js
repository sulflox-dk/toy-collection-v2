/**
 * Core Layout Logic
 * Handles sidebar persistence, global toggling, and mobile behavior.
 */
document.addEventListener('DOMContentLoaded', () => {
	initMobileSidebar();
	initSidebarPersistence();
	initGlobalToggle();
});

/**
 * 1. SIDEBAR PERSISTENCE (Memory)
 * Remembers which menus were open using localStorage.
 */
function initSidebarPersistence() {
	const STORAGE_KEY = 'sidebar_open_menus';

	// A. Restore State on Load
	let openMenus = [];
	try {
		openMenus = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
	} catch (e) {
		console.warn('Sidebar state corrupted, resetting.', e);
		localStorage.removeItem(STORAGE_KEY);
	}

	// Ensure it's actually an array before looping
	if (Array.isArray(openMenus)) {
		openMenus.forEach((id) => {
			const collapseEl = document.getElementById(id);
			if (collapseEl) {
				// Add 'show' class to open the menu
				collapseEl.classList.add('show');

				// Find the trigger button and remove 'collapsed' class so arrow rotates
				const trigger = document.querySelector(`[href="#${id}"]`);
				if (trigger) {
					trigger.classList.remove('collapsed');
					trigger.setAttribute('aria-expanded', 'true');
				}
			}
		});
	}

	// B. Save State on Change
	const collapses = document.querySelectorAll('.sidebar .collapse');
	collapses.forEach((el) => {
		// When a menu opens
		el.addEventListener('shown.bs.collapse', () => {
			let currentOpen = [];
			try {
				currentOpen = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
				if (!Array.isArray(currentOpen)) currentOpen = [];
			} catch (e) {
				currentOpen = [];
			}

			if (!currentOpen.includes(el.id)) {
				currentOpen.push(el.id);
				localStorage.setItem(STORAGE_KEY, JSON.stringify(currentOpen));
			}
		});

		// When a menu closes
		el.addEventListener('hidden.bs.collapse', () => {
			let currentOpen = [];
			try {
				currentOpen = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
				if (!Array.isArray(currentOpen)) currentOpen = [];
			} catch (e) {
				currentOpen = [];
			}

			currentOpen = currentOpen.filter((id) => id !== el.id);
			localStorage.setItem(STORAGE_KEY, JSON.stringify(currentOpen));
		});
	});
}

/**
 * 2. GLOBAL TOGGLE (Expand/Collapse All)
 */
function initGlobalToggle() {
	const btn = document.getElementById('btn-toggle-all');
	if (!btn) return;

	const collapses = document.querySelectorAll('.sidebar .collapse');
	const icon = btn.querySelector('i');

	// 1. Check initial state on page load
	const allOpenOnLoad = Array.from(collapses).every((el) =>
		el.classList.contains('show'),
	);

	let allExpanded = allOpenOnLoad;

	// 2. Sync button UI with initial state
	if (allExpanded) {
		icon.classList.remove('fa-angles-right');
		icon.classList.add('fa-angles-down');
		btn.title = 'Collapse All';
	}

	// 3. Click Handler
	btn.addEventListener('click', () => {
		allExpanded = !allExpanded;

		collapses.forEach((el) => {
			const bsCollapse = bootstrap.Collapse.getOrCreateInstance(el, {
				toggle: false,
			});

			if (allExpanded) {
				bsCollapse.show();
			} else {
				bsCollapse.hide();
			}
		});

		// Toggle Icon & Title
		if (allExpanded) {
			icon.classList.remove('fa-angles-right');
			icon.classList.add('fa-angles-down');
			btn.title = 'Collapse All';
		} else {
			icon.classList.remove('fa-angles-down');
			icon.classList.add('fa-angles-right');
			btn.title = 'Expand All';
		}
	});
}

/**
 * 3. MOBILE SIDEBAR
 */
function initMobileSidebar() {
	const offcanvasEl = document.getElementById('offcanvasSidebar');
	const sidebarMenu = document.getElementById('sidebarMenu');

	if (!offcanvasEl || !sidebarMenu) return;

	offcanvasEl.addEventListener('show.bs.offcanvas', () => {
		const body = offcanvasEl.querySelector('.offcanvas-body');
		if (body.innerHTML.trim() === '') {
			body.innerHTML = sidebarMenu.innerHTML;
			const mobileToggleBtn = body.querySelector('#btn-toggle-all');
			if (mobileToggleBtn) mobileToggleBtn.remove();
		}
	});
}

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
	const openMenus = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');

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

	// B. Save State on Change
	const collapses = document.querySelectorAll('.sidebar .collapse');
	collapses.forEach((el) => {
		// When a menu opens
		el.addEventListener('shown.bs.collapse', () => {
			const currentOpen = JSON.parse(
				localStorage.getItem(STORAGE_KEY) || '[]',
			);
			if (!currentOpen.includes(el.id)) {
				currentOpen.push(el.id);
				localStorage.setItem(STORAGE_KEY, JSON.stringify(currentOpen));
			}
		});

		// When a menu closes
		el.addEventListener('hidden.bs.collapse', () => {
			let currentOpen = JSON.parse(
				localStorage.getItem(STORAGE_KEY) || '[]',
			);
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
	// Returns true ONLY if every single menu is currently open
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
			// Clone the sidebar content
			body.innerHTML = sidebarMenu.innerHTML;

			// Remove the "Expand All" button from mobile view (optional, but cleaner)
			const mobileToggleBtn = body.querySelector('#btn-toggle-all');
			if (mobileToggleBtn) mobileToggleBtn.remove();

			// Re-attach persistence logic for the mobile menu clones?
			// Actually, usually mobile menus should start fresh or match desktop.
			// Since we clone HTML *with* the 'show' classes already applied from desktop state,
			// the mobile menu will actually inherit the open/closed state of the desktop menu!
		}
	});
}

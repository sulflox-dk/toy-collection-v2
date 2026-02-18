document.addEventListener('DOMContentLoaded', () => {
	// UI Elements
	const modalEl = document.getElementById('media-modal');
	const formEl = document.getElementById('media-form');
	const idInput = document.getElementById('media-id');

	// Toggleable Sections
	const fileGroup = document.getElementById('file-input-group');
	const previewContainer = document.getElementById('preview-container');
	const previewImg = document.getElementById('preview-image');

	// Buttons (Delete button removed)
	const saveBtnText = document.getElementById('btn-save-text');
	const uploadIcon = document.getElementById('icon-upload');

	// =====================================================================
	// 1. CUSTOM SUBMIT HANDLER
	// =====================================================================
	formEl.addEventListener('submit', async (e) => {
		const isEdit = idInput.value !== '';

		// If it's an Edit (no file input), let EntityManager handle it.
		if (isEdit) return;

		// --- UPLOAD MODE ---
		e.preventDefault();
		e.stopImmediatePropagation();

		const singleFileInput = document.getElementById('media-file-input');
		if (singleFileInput && !singleFileInput.files.length) {
			alert('Please select a file.');
			return;
		}

		const formData = new FormData(formEl);
		const baseUrl = typeof SITE_URL !== 'undefined' ? SITE_URL : '/';
		const csrfToken = document.querySelector(
			'meta[name="csrf-token"]',
		)?.content;

		const btn = formEl.querySelector('button[type="submit"]');
		const originalText = btn.innerHTML;
		btn.disabled = true;
		btn.innerHTML =
			'<i class="fa-solid fa-spinner fa-spin"></i> Uploading...';

		try {
			// FIX 1: Point to media-file route instead of media
			const response = await fetch(baseUrl + 'media-file', {
				method: 'POST',
				body: formData,
				headers: { 'X-CSRF-Token': csrfToken || '' },
			});

			const result = await response.json();
			if (!response.ok) throw new Error(result.error || 'Upload failed');

			const activeModal = bootstrap.Modal.getInstance(modalEl);
			if (activeModal) {
				activeModal.hide();
			}

			formEl.reset();
			manager.loadList();
		} catch (error) {
			console.error(error);
			alert(error.message);
		} finally {
			btn.disabled = false;
			btn.innerHTML = originalText;
		}
	});

	// =====================================================================
	// 2. INITIALIZE ENTITY MANAGER
	// =====================================================================
	const manager = new EntityManager('media', {
		mode: 'html',
		// FIX 2: Point to media-file route
		endpoint: '/media-file',
		listUrl: '/media-file/list',
		ui: {
			modalId: 'media-modal',
			formId: 'media-form',
			gridId: 'media-grid',
			btnAddId: 'btn-add-media',
			searchInputId: 'search-input',
		},
	});

	// =====================================================================
	// 3. MODAL STATE HANDLER (Upload vs Edit UI)
	// =====================================================================
	modalEl.addEventListener('show.bs.modal', () => {
		// Hide Dropzone if open
		const uploadZone = document.getElementById('upload-zone');
		if (uploadZone) uploadZone.classList.add('d-none');

		const isEdit = idInput.value && idInput.value !== '';

		if (isEdit) {
			// EDIT MODE UI
			if (fileGroup) fileGroup.classList.add('d-none');
			if (previewContainer) previewContainer.classList.remove('d-none');

			if (saveBtnText) saveBtnText.textContent = 'Save Changes';
			if (uploadIcon) uploadIcon.classList.add('d-none');
		} else {
			// UPLOAD MODE UI
			document.querySelector('.modal-title').textContent = 'Upload New File';
			formEl.reset();
			idInput.value = '';

			if (fileGroup) fileGroup.classList.remove('d-none');
			if (previewContainer) previewContainer.classList.add('d-none');

			// FIX: Explicitly clear the old image source so it doesn't linger in the DOM
			if (previewImg) {
				previewImg.removeAttribute('src');
			}

			if (saveBtnText) saveBtnText.textContent = 'Upload';
			if (uploadIcon) uploadIcon.classList.remove('d-none');
		}
	});

	// =====================================================================
	// 4. MULTIPLE UPLOAD LOGIC
	// =====================================================================
	const btnToggleDropzone = document.getElementById('btn-toggle-dropzone');
	const uploadZone = document.getElementById('upload-zone');
	const fileInput = document.getElementById('file-input');
	const progressBar = document.querySelector('#upload-progress .progress-bar');
	const progressContainer = document.getElementById('upload-progress');

	if (btnToggleDropzone && uploadZone) {
		btnToggleDropzone.addEventListener('click', () => {
			uploadZone.classList.toggle('d-none');
		});
	}

	if (fileInput)
		fileInput.addEventListener('change', (e) => handleFiles(e.target.files));

	if (uploadZone) {
		uploadZone.addEventListener('dragover', (e) => {
			e.preventDefault();
			uploadZone.classList.add('border-primary');
		});
		uploadZone.addEventListener('dragleave', (e) => {
			e.preventDefault();
			uploadZone.classList.remove('border-primary');
		});
		uploadZone.addEventListener('drop', (e) => {
			e.preventDefault();
			uploadZone.classList.remove('border-primary');
			handleFiles(e.dataTransfer.files);
		});
	}

	async function handleFiles(files) {
		if (files.length === 0) return;

		// --- Max Upload Limit Check ---
		const MAX_UPLOAD_LIMIT = 20;
		if (files.length > MAX_UPLOAD_LIMIT) {
			alert(
				`Please select a maximum of ${MAX_UPLOAD_LIMIT} files at a time. You selected ${files.length}.`,
			);
			// Reset the input so the user can select again
			if (fileInput) fileInput.value = '';
			return; // Abort the upload process
		}

		if (progressContainer) progressContainer.classList.remove('d-none');
		let completed = 0;

		const baseUrl = typeof SITE_URL !== 'undefined' ? SITE_URL : '/';
		const csrfToken = document.querySelector(
			'meta[name="csrf-token"]',
		)?.content;

		for (let file of files) {
			const formData = new FormData();
			formData.append('file', file);

			try {
				// FIX 3: Point to media-file route
				const response = await fetch(baseUrl + 'media-file', {
					method: 'POST',
					body: formData,
					headers: { 'X-CSRF-Token': csrfToken || '' },
				});
				if (!response.ok) throw new Error('Upload failed');
			} catch (error) {
				console.error(error);
				alert(`Failed to upload ${file.name}`);
			}

			completed++;
			if (progressBar) {
				const percent = (completed / files.length) * 100;
				progressBar.style.width = `${percent}%`;
			}
		}

		setTimeout(() => {
			if (progressContainer) progressContainer.classList.add('d-none');
			if (progressBar) progressBar.style.width = '0%';
			if (uploadZone) uploadZone.classList.add('d-none');
			if (fileInput) fileInput.value = '';

			manager.loadList();
		}, 800);
	}

	// =====================================================================
	// 5. IMAGE PREVIEW HELPER (For Edits)
	// =====================================================================
	document.addEventListener('click', (e) => {
		const editBtn = e.target.closest('.btn-edit');
		if (!editBtn) return;

		const card = editBtn.closest('.card');
		if (!card) return;

		const thumbImg = card.querySelector('img');
		const previewImg = document.getElementById('preview-image');

		if (previewImg) {
			if (thumbImg) {
				previewImg.src = thumbImg.src;
				previewImg.classList.remove('d-none');
			} else {
				previewImg.src = '';
				previewImg.classList.add('d-none');
			}
		}
	});

	// =====================================================================
	// 6. LIGHTBOX / OVERLAY GALLERY
	// =====================================================================
	const lightboxModalEl = document.getElementById('lightbox-modal');
	const lightboxImg = document.getElementById('lightbox-img');
	const lightboxTitle = document.getElementById('lightbox-title');
	const lightboxPrev = document.getElementById('lightbox-prev');
	const lightboxNext = document.getElementById('lightbox-next');
	const lightboxPdfContainer = document.getElementById(
		'lightbox-pdf-container',
	);
	const lightboxPdfLink = document.getElementById('lightbox-pdf-link');
	const lightboxBtnEdit = document.getElementById('lightbox-btn-edit');
	const lightboxBtnDelete = document.getElementById('lightbox-btn-delete');

	let currentCards = [];
	let currentLightboxIndex = -1;

	// 6A. Open Lightbox on Grid Click
	document.addEventListener('click', (e) => {
		const trigger = e.target.closest('.lightbox-trigger');
		if (!trigger) return;

		const card = trigger.closest('.file-card');
		if (!card) return;

		// Grab all cards currently visible in the grid (respects active search/filters)
		currentCards = Array.from(document.querySelectorAll('.file-card'));
		currentLightboxIndex = currentCards.indexOf(card);

		if (currentLightboxIndex > -1) {
			updateLightboxView();
			const lbModal = bootstrap.Modal.getOrCreateInstance(lightboxModalEl);
			lbModal.show();
		}
	});

	// 6B. Update Lightbox Content
	function updateLightboxView() {
		if (
			currentLightboxIndex < 0 ||
			currentLightboxIndex >= currentCards.length
		)
			return;

		const card = currentCards[currentLightboxIndex];

		// Extract data hidden inside the card's edit button
		const editBtn = card.querySelector('.btn-edit');
		if (!editBtn) return;

		const data = JSON.parse(editBtn.getAttribute('data-json'));
		const baseUrl = typeof SITE_URL !== 'undefined' ? SITE_URL : '/';
		const fullPath = baseUrl + data.filepath;

		// Set Title
		lightboxTitle.textContent = data.title || data.original_name;

		// Display Image OR PDF Icon
		const isImage = [
			'image/jpeg',
			'image/png',
			'image/gif',
			'image/webp',
		].includes(data.file_type.toLowerCase());

		if (isImage) {
			lightboxImg.src = fullPath;
			lightboxImg.classList.remove('d-none');
			lightboxPdfContainer.classList.add('d-none');
		} else {
			lightboxImg.src = '';
			lightboxImg.classList.add('d-none');
			lightboxPdfContainer.classList.remove('d-none');
			lightboxPdfLink.href = fullPath;
		}

		// Toggle Arrow Visibility (hide Prev if at first item, hide Next if at last)
		lightboxPrev.style.visibility =
			currentLightboxIndex > 0 ? 'visible' : 'hidden';
		lightboxNext.style.visibility =
			currentLightboxIndex < currentCards.length - 1 ? 'visible' : 'hidden';
	}

	// 6C. Navigation Clicks
	if (lightboxPrev) {
		lightboxPrev.addEventListener('click', () => {
			if (currentLightboxIndex > 0) {
				currentLightboxIndex--;
				updateLightboxView();
			}
		});
	}
	if (lightboxNext) {
		lightboxNext.addEventListener('click', () => {
			if (currentLightboxIndex < currentCards.length - 1) {
				currentLightboxIndex++;
				updateLightboxView();
			}
		});
	}

	// 6D. Keyboard Navigation (Arrows to move, Esc is handled natively by Bootstrap)
	document.addEventListener('keydown', (e) => {
		// Only run if the lightbox is actually open
		if (!lightboxModalEl.classList.contains('show')) return;

		if (e.key === 'ArrowLeft' && currentLightboxIndex > 0) {
			currentLightboxIndex--;
			updateLightboxView();
		} else if (
			e.key === 'ArrowRight' &&
			currentLightboxIndex < currentCards.length - 1
		) {
			currentLightboxIndex++;
			updateLightboxView();
		}
	});

	// 6E. Action Buttons (Delegate clicks to the actual grid buttons)
	if (lightboxBtnEdit) {
		lightboxBtnEdit.addEventListener('click', () => {
			const lbModal = bootstrap.Modal.getInstance(lightboxModalEl);
			if (lbModal) lbModal.hide(); // Close Lightbox

			// "Click" the real edit button on the card behind the scenes
			const card = currentCards[currentLightboxIndex];
			const realEditBtn = card.querySelector('.btn-edit');
			if (realEditBtn) realEditBtn.click();
		});
	}

	if (lightboxBtnDelete) {
		lightboxBtnDelete.addEventListener('click', () => {
			const lbModal = bootstrap.Modal.getInstance(lightboxModalEl);
			if (lbModal) lbModal.hide(); // Close Lightbox

			// "Click" the real delete button on the card behind the scenes
			const card = currentCards[currentLightboxIndex];
			const realDeleteBtn = card.querySelector('.btn-delete');
			if (realDeleteBtn) realDeleteBtn.click();
		});
	}

	// =====================================================================
	// 7. TAG CLOUD LOGIC
	// =====================================================================
	const tagCloud = document.getElementById('tag-cloud');
	const selectedTagIdsInput = document.getElementById('selected-tag-ids');

	function escapeHtml(str) {
		const div = document.createElement('div');
		div.textContent = str;
		return div.innerHTML;
	}

	function renderTagCloud(selectedIds = []) {
		if (!tagCloud || typeof ALL_MEDIA_TAGS === 'undefined') return;

		// Ensure selectedIds are integers
		const activeIds = selectedIds.map((id) => parseInt(id, 10));

		tagCloud.innerHTML = ALL_MEDIA_TAGS.map((tag) => {
			const isActive = activeIds.includes(parseInt(tag.id, 10));

			const bgClass = isActive
				? 'bg-primary text-white shadow-sm border border-primary'
				: 'bg-light text-dark border border-secondary-subtle';

			return `
                <span class="badge ${bgClass} p-2 tag-badge"
                      data-id="${parseInt(tag.id, 10)}"
                      style="cursor: pointer; user-select: none; font-weight: normal; font-size: 0.85rem; transition: all 0.2s;">
                    ${escapeHtml(tag.name)}
                </span>`;
		}).join('');

		selectedTagIdsInput.value = activeIds.join(',');
	}

	// Toggle tags on click
	if (tagCloud) {
		tagCloud.addEventListener('click', (e) => {
			const badge = e.target.closest('.tag-badge');
			if (!badge) return;

			const tagId = parseInt(badge.dataset.id, 10);
			let currentIds = selectedTagIdsInput.value
				? selectedTagIdsInput.value.split(',').map((id) => parseInt(id, 10))
				: [];

			if (currentIds.includes(tagId)) {
				currentIds = currentIds.filter((id) => id !== tagId); // Remove
			} else {
				currentIds.push(tagId); // Add
			}

			renderTagCloud(currentIds);
		});
	}

	// =====================================================================
	// UPDATE MODAL HANDLER FOR TAGS
	// =====================================================================
	modalEl.addEventListener('show.bs.modal', () => {
		const uploadZone = document.getElementById('upload-zone');
		if (uploadZone) uploadZone.classList.add('d-none');

		// Fordi EntityManager udfylder formen FØR den åbner modallen,
		// er dette den mest sikre måde at tjekke, om vi redigerer.
		const isEdit = idInput.value && idInput.value !== '';

		if (isEdit) {
			// EDIT MODE UI
			if (fileGroup) fileGroup.classList.add('d-none');
			if (previewContainer) previewContainer.classList.remove('d-none');
			if (saveBtnText) saveBtnText.textContent = 'Save Changes';
			if (uploadIcon) uploadIcon.classList.add('d-none');

			// Find knappen i griddet, der matcher dette ID, og træk tag_ids ud fra dens JSON
			const editBtn = document.querySelector(
				`.btn-edit[data-id="${idInput.value}"]`,
			);
			if (editBtn) {
				const data = JSON.parse(editBtn.getAttribute('data-json') || '{}');
				renderTagCloud(data.tag_ids || []);
			}
		} else {
			// UPLOAD MODE UI
			document.querySelector('.modal-title').textContent = 'Upload New File';
			formEl.reset();
			idInput.value = '';

			if (fileGroup) fileGroup.classList.remove('d-none');
			if (previewContainer) previewContainer.classList.add('d-none');
			if (previewImg) previewImg.removeAttribute('src');
			if (saveBtnText) saveBtnText.textContent = 'Upload';
			if (uploadIcon) uploadIcon.classList.remove('d-none');

			// Reset tags
			renderTagCloud([]);
		}
	});
});

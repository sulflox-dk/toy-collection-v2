/**
 * MediaPicker — Reusable media picker, uploader, and thumbnail manager.
 *
 * Works with any entity type via the polymorphic media_links table.
 * Usage:
 *   MediaPicker.open('catalog_toys', 42);
 *   MediaPicker.refreshThumbnails('collection_toy_items', 7);
 *   MediaPicker.initDragAndDrop();
 */
const MediaPicker = {
	currentEntityType: null,
	currentEntityId: null,

	// =====================================================================
	// DRAG AND DROP
	// =====================================================================
	initDragAndDrop() {
		const dropZone = document.getElementById('mediaDropZone');
		const fileInput = document.getElementById('mediaUploadInput');

		if (!dropZone || !fileInput) return;

		['dragenter', 'dragover', 'dragleave', 'drop'].forEach((eventName) => {
			dropZone.addEventListener(
				eventName,
				(e) => {
					e.preventDefault();
					e.stopPropagation();
				},
				false,
			);
		});

		['dragenter', 'dragover'].forEach((eventName) => {
			dropZone.addEventListener(
				eventName,
				() => {
					dropZone.classList.add('bg-light', 'border-primary');
				},
				false,
			);
		});

		['dragleave', 'drop'].forEach((eventName) => {
			dropZone.addEventListener(
				eventName,
				() => {
					dropZone.classList.remove('bg-light', 'border-primary');
				},
				false,
			);
		});

		dropZone.addEventListener(
			'drop',
			(e) => {
				const dt = e.dataTransfer;
				const files = dt.files;

				if (files && files.length > 0) {
					fileInput.files = files;
					this.handleFileUpload(fileInput);
				}
			},
			false,
		);
	},

	// =====================================================================
	// PICKER OVERLAY
	// =====================================================================
	open(entityType, entityId) {
		this.currentEntityType = entityType;
		this.currentEntityId = entityId;

		const overlay = document.getElementById('mediaPickerOverlay');
		if (overlay) {
			overlay.classList.remove('d-none');
			overlay.classList.add('d-flex');
		}
	},

	close() {
		const overlay = document.getElementById('mediaPickerOverlay');
		if (overlay) {
			overlay.classList.remove('d-flex');
			overlay.classList.add('d-none');
		}

		const fileInput = document.getElementById('mediaUploadInput');
		if (fileInput) fileInput.value = '';

		const searchInput = document.getElementById('mediaSearchInput');
		if (searchInput) searchInput.value = '';

		const searchResults = document.getElementById('mediaSearchResults');
		if (searchResults) {
			searchResults.innerHTML =
				'<div class="text-center text-muted mt-5">Type to search your image library...</div>';
		}
	},

	// =====================================================================
	// FILE UPLOAD
	// =====================================================================
	async handleFileUpload(inputElement) {
		const files = inputElement.files;
		if (files.length === 0) return;

		const dropZone = document.getElementById('mediaDropZone');

		// Hide existing content safely without deleting it
		const originalDisplayStates = [];
		Array.from(dropZone.children).forEach((child) => {
			originalDisplayStates.push({
				el: child,
				display: child.style.display,
			});
			child.style.display = 'none';
		});

		// Add spinner
		const spinner = document.createElement('div');
		spinner.id = 'uploadSpinner';
		spinner.className =
			'd-flex flex-column align-items-center justify-content-center h-100 text-muted';
		spinner.innerHTML =
			'<i class="fa-solid fa-spinner fa-spin fa-3x mb-3"></i><h5>Uploading...</h5>';
		dropZone.appendChild(spinner);

		const csrfToken =
			document
				.querySelector('meta[name="csrf-token"]')
				?.getAttribute('content') || '';

		try {
			for (let i = 0; i < files.length; i++) {
				const formData = new FormData();
				formData.append('file', files[i]);
				formData.append('entity_type', this.currentEntityType);
				formData.append('entity_id', this.currentEntityId);

				const response = await fetch(SITE_URL + 'media-file', {
					method: 'POST',
					headers: { 'X-CSRF-TOKEN': csrfToken },
					body: formData,
				});

				if (!response.ok) {
					const err = await response.json();
					console.error('Upload error:', err);
				}
			}

			this.close();
			this.refreshThumbnails(this.currentEntityType, this.currentEntityId);
		} catch (error) {
			console.error(error);
			alert('Upload failed due to a network error.');
		} finally {
			const activeSpinner = document.getElementById('uploadSpinner');
			if (activeSpinner) activeSpinner.remove();

			originalDisplayStates.forEach((item) => {
				item.el.style.display = item.display;
			});
		}
	},

	// =====================================================================
	// SEARCH LIBRARY
	// =====================================================================
	async searchLibrary(query) {
		const resultsContainer = document.getElementById('mediaSearchResults');

		if (query.length < 2) {
			resultsContainer.innerHTML =
				'<div class="text-center text-muted mt-5">Type to search your image library...</div>';
			return;
		}

		resultsContainer.innerHTML =
			'<div class="text-center text-muted mt-5"><i class="fa-solid fa-spinner fa-spin me-2"></i>Searching...</div>';

		try {
			const response = await fetch(
				`${SITE_URL}media-file/search-json?q=${encodeURIComponent(query)}`,
			);
			const data = await response.json();

			if (data.length === 0) {
				resultsContainer.innerHTML =
					'<div class="text-center text-muted mt-5">No images found matching your search.</div>';
				return;
			}

			const container = document.createElement('div');
			container.className = 'row g-2';
			data.forEach((file) => {
				const title = file.title || file.filename;
				const col = document.createElement('div');
				col.className = 'col-4 col-md-3';

				const card = document.createElement('div');
				card.className = 'card h-100 border shadow-sm';
				card.style.cssText =
					'cursor: pointer; transition: transform 0.1s;';
				card.addEventListener(
					'mouseover',
					() => (card.style.transform = 'scale(1.05)'),
				);
				card.addEventListener(
					'mouseout',
					() => (card.style.transform = 'scale(1)'),
				);
				card.addEventListener('click', () =>
					MediaPicker.linkExistingMedia(file.id),
				);

				const img = document.createElement('img');
				img.src = SITE_URL + file.filepath;
				img.className = 'card-img-top';
				img.style.cssText =
					'height: 100px; object-fit: contain; padding: 5px;';
				img.loading = 'lazy';

				const body = document.createElement('div');
				body.className =
					'card-body p-1 text-center text-truncate small text-muted bg-light';
				body.style.fontSize = '0.7rem';
				body.title = title;
				body.textContent = title;

				card.appendChild(img);
				card.appendChild(body);
				col.appendChild(card);
				container.appendChild(col);
			});
			resultsContainer.innerHTML = '';
			resultsContainer.appendChild(container);
		} catch (error) {
			resultsContainer.innerHTML =
				'<div class="text-danger mt-5 text-center">Search failed to load.</div>';
		}
	},

	// =====================================================================
	// LINK EXISTING MEDIA
	// =====================================================================
	async linkExistingMedia(mediaFileId) {
		const csrfToken =
			document
				.querySelector('meta[name="csrf-token"]')
				?.getAttribute('content') || '';

		const formData = new FormData();
		formData.append('media_file_id', mediaFileId);
		formData.append('entity_type', this.currentEntityType);
		formData.append('entity_id', this.currentEntityId);

		try {
			const response = await fetch(SITE_URL + 'media-file/link', {
				method: 'POST',
				headers: { 'X-CSRF-TOKEN': csrfToken },
				body: formData,
			});

			const result = await response.json();
			if (result.success) {
				this.close();
				this.refreshThumbnails(
					this.currentEntityType,
					this.currentEntityId,
				);
			}
		} catch (error) {
			console.error('Failed to link media', error);
		}
	},

	// =====================================================================
	// THUMBNAIL MANAGEMENT
	// =====================================================================
	async refreshThumbnails(entityType, entityId) {
		const container = document.getElementById(
			`preview-${entityType}-${entityId}`,
		);
		if (!container) return;

		container.innerHTML =
			'<div class="w-100 text-center py-3"><i class="fa-solid fa-spinner fa-spin text-muted"></i></div>';

		try {
			const response = await fetch(
				`${SITE_URL}media-file/thumbnails?type=${entityType}&id=${entityId}`,
			);
			const images = await response.json();

			if (images.length === 0) {
				container.innerHTML =
					'<div class="text-muted small w-100 text-center py-3 bg-light border rounded border-dashed">No images attached yet.</div>';
				return;
			}

			const successAlert = document.getElementById('step3-success-alert');
			if (successAlert) {
				successAlert.style.opacity = '0';
				setTimeout(() => successAlert.remove(), 300);
			}

			container.innerHTML = '';

			const wrapper = document.createElement('div');
			wrapper.className = 'vstack gap-3 mt-3 w-100';

			const template = document.getElementById('mediaEditRowTemplate');

			images.forEach((img) => {
				const clone = template.content.cloneNode(true);
				const row = clone.querySelector('.media-edit-row');

				row.dataset.mediaId = img.media_file_id;

				clone.querySelector('.preview-img').src =
					SITE_URL + img.filepath;
				clone.querySelector('.meta-title').value = img.title || '';
				clone.querySelector('.meta-alt').value = img.alt_text || '';
				clone.querySelector('.meta-desc').value = img.description || '';

				clone.querySelector('.tag-container').innerHTML =
					this.buildTagPills(img.tag_ids || []);

				clone.querySelector('.btn-unlink').onclick = () => {
					this.unlinkMedia(img.link_id, entityType, entityId);
				};

				this.attachRowListeners(row, img.media_file_id);

				wrapper.appendChild(clone);
			});

			container.appendChild(wrapper);
		} catch (error) {
			container.innerHTML =
				'<div class="text-danger small w-100 text-center">Error loading images</div>';
		}
	},

	// =====================================================================
	// TAG PILLS
	// =====================================================================
	buildTagPills(activeTagIds) {
		const container = document.getElementById('media-manager-container');
		const tagsJson = container.dataset.tags || '[]';
		let allTags = [];
		try {
			allTags = JSON.parse(tagsJson);
		} catch (e) {}

		if (allTags.length === 0)
			return '<span class="text-muted small">No tags available.</span>';

		const activeIds = activeTagIds.map((id) => parseInt(id));

		return allTags
			.map((tag) => {
				const isActive = activeIds.includes(parseInt(tag.id));
				const bgClass = isActive
					? 'bg-dark text-white'
					: 'bg-light text-dark border';
				return `
                <span class="badge rounded-pill ${bgClass} tag-pill p-2"
                      data-id="${tag.id}"
                      style="cursor: pointer; user-select: none; transition: all 0.2s;">
                    ${tag.name}
                </span>`;
			})
			.join('');
	},

	// =====================================================================
	// AUTO-SAVE LISTENERS
	// =====================================================================
	attachRowListeners(rowElement, mediaId) {
		rowElement.querySelectorAll('.tag-pill').forEach((pill) => {
			pill.addEventListener('click', () => {
				if (pill.classList.contains('bg-dark')) {
					pill.classList.replace('bg-dark', 'bg-light');
					pill.classList.replace('text-white', 'text-dark');
					pill.classList.add('border');
				} else {
					pill.classList.replace('bg-light', 'bg-dark');
					pill.classList.replace('text-dark', 'text-white');
					pill.classList.remove('border');
				}
				this.saveMetadata(mediaId, rowElement);
			});
		});

		rowElement.querySelectorAll('.meta-input').forEach((input) => {
			input.addEventListener('change', () => {
				this.saveMetadata(mediaId, rowElement);
			});
		});
	},

	async saveMetadata(mediaId, rowElement) {
		const title = rowElement.querySelector('.meta-title').value;
		const alt = rowElement.querySelector('.meta-alt').value;
		const desc = rowElement.querySelector('.meta-desc').value;

		const activePills = rowElement.querySelectorAll('.tag-pill.bg-dark');
		const selectedTags = Array.from(activePills)
			.map((p) => p.dataset.id)
			.join(',');

		const indicator = rowElement.querySelector('.save-indicator');

		const formData = new FormData();
		formData.append('_method', 'PUT');
		formData.append('title', title);
		formData.append('alt_text', alt);
		formData.append('description', desc);
		formData.append('tag_ids', selectedTags);

		const csrfToken =
			document
				.querySelector('meta[name="csrf-token"]')
				?.getAttribute('content') || '';

		try {
			const response = await fetch(`${SITE_URL}media-file/${mediaId}`, {
				method: 'POST',
				headers: { 'X-CSRF-TOKEN': csrfToken },
				body: formData,
			});

			if (response.ok && indicator) {
				indicator.style.opacity = '1';
				setTimeout(() => (indicator.style.opacity = '0'), 2000);
			}
		} catch (error) {
			console.error('Save metadata failed:', error);
		}
	},

	// =====================================================================
	// UNLINK
	// =====================================================================
	async unlinkMedia(linkId, entityType, entityId) {
		if (
			!confirm(
				'Are you sure you want to remove this photo from this item?',
			)
		)
			return;

		const csrfToken =
			document
				.querySelector('meta[name="csrf-token"]')
				?.getAttribute('content') || '';
		const formData = new FormData();
		formData.append('link_id', linkId);

		try {
			const response = await fetch(`${SITE_URL}media-file/unlink`, {
				method: 'POST',
				headers: { 'X-CSRF-TOKEN': csrfToken },
				body: formData,
			});

			if (response.ok) {
				this.refreshThumbnails(entityType, entityId);
			}
		} catch (error) {
			console.error('Failed to unlink media:', error);
		}
	},
};

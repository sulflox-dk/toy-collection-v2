<!-- =======================================================================
   Entity Photo Modal — Shared "manage this row's photo" modal for simple
   CRUD list pages (Universe/Manufacturer/ToyLine/EntertainmentSource).

   One instance per page. Each row's "Photo" button calls
   openEntityPhotoModal(entityType, entityId, entityName); the preview
   container's id is re-targeted to preview-{entityType}-{entityId} on
   every open so MediaPicker.refreshThumbnails() finds the right one.

   Prerequisite: core/media-picker.js (loaded globally in layout.php).
   ======================================================================= -->

<div class="modal fade" id="entity-photo-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-camera me-2"></i>Photo — <span id="entity-photo-modal-name"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="media-manager-container" data-tags="[]">
                <div class="d-flex justify-content-end mb-3">
                    <button class="btn btn-outline-dark btn-sm" onclick="MediaPicker.open(window.currentPhotoEntityType, window.currentPhotoEntityId)">
                        <i class="fa-solid fa-plus me-1"></i> Add Photo
                    </button>
                </div>
                <div class="d-flex flex-wrap gap-2 entity-photo-preview-container">
                    <div class="text-muted small w-100 text-center py-3 bg-light border rounded border-dashed">No images attached yet.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->renderPartial('common/media_manager') ?>

<script>
function openEntityPhotoModal(entityType, entityId, entityName) {
    window.currentPhotoEntityType = entityType;
    window.currentPhotoEntityId = entityId;
    document.getElementById('entity-photo-modal-name').textContent = entityName;
    document.querySelector('.entity-photo-preview-container').id = `preview-${entityType}-${entityId}`;
    MediaPicker.refreshThumbnails(entityType, entityId);
    new bootstrap.Modal(document.getElementById('entity-photo-modal')).show();
}

document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('entity-photo-modal');
    if (!modalEl) return;

    // Without this, dropping a file onto #mediaDropZone never gets a
    // preventDefault() — the browser falls back to its default "open this
    // file" behavior instead of triggering the upload.
    MediaPicker.initDragAndDrop();

    // The row's own thumbnail is rendered server-side at list-load time,
    // so it won't reflect a photo added/removed inside this modal until
    // the list is reloaded — do that whenever the modal closes.
    modalEl.addEventListener('hidden.bs.modal', () => {
        const manager = window.currentEntityManager;
        if (manager && typeof manager.loadList === 'function') {
            manager.loadList(manager.currentParams);
        }
    });
});
</script>

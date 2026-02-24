<div class="modal-header bg-dark text-white border-0">
    <h5 class="modal-title">
        <i class="fa-solid fa-plus me-2"></i>Add to Collection
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body p-4 d-flex flex-column bg-light" style="min-height: 400px;">
    <h5 class="text-center mb-4 fw-bold text-dark">Which toy are you adding?</h5>

    <div class="input-group input-group-lg mb-4">
        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
        <input type="text" class="form-control border-start-0" id="catalogToySearch"
               placeholder="Search catalog toys by name..."
               autocomplete="off" autofocus>
    </div>

    <div id="catalogSearchResults" class="flex-grow-1 overflow-auto">
        <div class="text-center text-muted mt-5">
            <i class="fa-solid fa-box-open fa-3x mb-3 opacity-25"></i>
            <p class="mb-0">Start typing to search your catalog...</p>
        </div>
    </div>
</div>

<div class="modal-footer bg-light border-top">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
</div>

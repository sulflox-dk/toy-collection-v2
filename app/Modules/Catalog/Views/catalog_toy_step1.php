<div class="modal-header border-0 pb-0 bg-white">
    <h5 class="modal-title text-muted"><i class="fa-solid fa-robot me-2"></i> Add New Catalog Toy</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body d-flex flex-column align-items-center pt-5">
    <h2 class="mb-5 fw-bold text-dark">Where does this new toy belong?</h2>

    <div class="container-fluid px-4 px-lg-5">
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-4 justify-content-center"> 
            <?php foreach($universes as $u): ?>
                <?php 
                    // Logic to find universe logos (assuming they are stored in assets/images/)
                    $slug = $u['slug'] ?? 'default';
                    $imgUrl = $baseUrl . 'assets/images/universe_' . $slug . '_square_logo.jpg';
                ?>
                <div class="col">
                    <div class="card h-100 universe-select-card shadow-sm rounded-4 overflow-hidden" onclick="CatalogWizard.goToStep2(<?= $u['id'] ?>)">
                        <div class="ratio ratio-1x1 bg-white p-3">
                            <img src="<?= $imgUrl ?>" alt="<?= htmlspecialchars($u['name']) ?>" 
                                 class="object-fit-contain"
                                 onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
                            
                            <div class="d-none w-100 h-100 d-flex align-items-center justify-content-center bg-light text-center">
                                <span class="fw-bold text-dark fs-5"><?= htmlspecialchars($u['name']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="modal-footer border-0 bg-light">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
</div>
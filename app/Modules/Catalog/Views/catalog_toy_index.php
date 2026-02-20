<?php
// Build the HTML for custom filters
ob_start(); 
?>
<div class="col-md-3 mb-2 mb-md-0">
    <select class="form-select data-filter" name="universe_id">
        <option value="">All Universes</option>
        <?php foreach ($universes as $u): ?>
            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="col-md-3 mb-2 mb-md-0">
    <select class="form-select data-filter" name="toy_line_id">
        <option value="">All Toy Lines</option>
        <?php foreach ($toyLines as $l): ?>
            <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['name']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="col-md-3 mb-2 mb-md-0">
    <select class="form-select data-filter" name="manufacturer_id">
        <option value="">All Manufacturers</option>
        <?php foreach ($manufacturers as $m): ?>
            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="col-md-3 mb-2 mb-md-0">
    <select class="form-select data-filter" name="product_type_id">
        <option value="">All Types</option>
        <?php foreach ($productTypes as $pt): ?>
            <option value="<?= $pt['id'] ?>"><?= htmlspecialchars($pt['name']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="col-md-3 mb-2 mb-md-0">
    <select class="form-select data-filter" name="ownership">
        <option value="">Status: All</option>
        <option value="owned">Owned</option>
        <option value="not_owned">Missing</option>
    </select>
</div>
<div class="col-md-3 mb-2 mb-md-0">
    <select class="form-select data-filter" name="image_status">
        <option value="">Image: All</option>
        <option value="has_image">Has Photo</option>
        <option value="missing_image">No Photo</option>
    </select>
</div>
<?php 
$customFiltersHTML = ob_get_clean();

// Pass it to the generic header
echo $this->renderPartial('common/index_header', [
    'title' => 'Catalog Toys',
    'entityKey' => 'catalog-toy',               
    'addBtnText' => 'Add Catalog Toy',          
    'searchPlaceholder' => 'Search...',
    'extraFilters' => $customFiltersHTML,
    'showViewMode' => true
]);
?>

<div class="modal fade" id="entity-modal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-vw-85 modal-dialog-centered">
        <div class="modal-content" id="entity-modal-content">
            </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    new EntityManager('catalog-toy', {
        mode: 'html',
        endpoint: '/catalog-toy',
        listUrl: '/catalog-toy/list',
    });
});
</script>
<?php
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
    <select class="form-select data-filter" name="acquisition_status_id">
        <option value="">All Statuses</option>
        <?php foreach ($statuses as $s): ?>
            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="col-md-3 mb-2 mb-md-0">
    <select class="form-select data-filter" name="storage_unit_id">
        <option value="">All Storage Units</option>
        <?php foreach ($storageUnits as $su): ?>
            <option value="<?= $su['id'] ?>"><?= htmlspecialchars($su['box_code'] ? $su['box_code'].' - '.$su['name'] : $su['name']) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div class="col-md-3 mb-2 mb-md-0">
    <select class="form-select data-filter" name="missing_parts">
        <option value="">Parts: All</option>
        <option value="complete">100% Complete</option>
        <option value="missing">Missing Parts</option>
    </select>
</div>

<div class="col-md-3 mb-2 mb-md-0">
    <select class="form-select data-filter" name="image_status">
        <option value="">My Photos: All</option>
        <option value="has_image">Has My Photo(s)</option>
        <option value="missing_image">Missing My Photo(s)</option>
    </select>
</div>
<?php 
$customFiltersHTML = ob_get_clean();

echo $this->renderPartial('common/index_header', [
    'title' => 'My Collection',
    'entityKey' => 'collection-toy',               
    'addBtnText' => 'Add to Collection',          
    'searchPlaceholder' => 'Search toys...',
    'extraFilters' => $customFiltersHTML,
    'showViewMode' => true
]);
?>

<div class="modal fade" id="entity-modal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-vw-85 modal-dialog-centered">
        <div class="modal-content" id="entity-modal-content"></div>
    </div>
</div>
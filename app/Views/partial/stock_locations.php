<?php
/**
 * @var array $stock_locations
 */
?>

<span class="d-flex justify-content-start add_stock_location">
    <button class="btn btn-outline-success mb-3" type="button"><i class="bi bi-plus-lg"></i>&nbsp;Add location</button> <!-- TODO-BS5 translate -->
</span>

<?php
$i = 0;

foreach ($stock_locations as $location => $location_data) {
    $location_id = $location_data['location_id'];
    $location_name = $location_data['location_name'];
    ++$i;
?>

<div class="col-12 col-lg-6 <?= $location_data['deleted'] ? 'd-none' : '' ?>">
    <label for="stock_location_<?= $i ?>" class="form-label"><?= lang('Config.stock_location') . " $i"; ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text"><?= $location_id ?>.</span>
        <input type="text" class="form-control stock_location valid_chars" name="stock_location<?= $location_id ?>" id="stock_location<?= $location_id ?>" value="<?= $location_name ?>" required <?= $location_data['deleted'] ? 'disabled' : '' ?>>
        <button class="btn btn-outline-danger remove_stock_location" type="button"><i class="bi bi-x-lg"></i></button>
    </div>
</div>

<?php } ?>

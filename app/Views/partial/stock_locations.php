<?php
/**
 * @var array $stock_locations
 */
?>

<?php
$i = 0;

foreach ($stock_locations as $location => $locationData) {
    $locationId = $locationData['location_id'];
    $locationName = $locationData['location_name'];
    ++$i;
?>

    <div class="form-group form-group-sm stock_location_row" data-location-id="<?= $locationId ?>" style="<?= $locationData['deleted'] ? 'display: none;' : 'display: block;' ?>">
        <span class="drag_handle glyphicon glyphicon-resize-vertical col-xs-1" style="cursor: move; padding-top: 0.5em;"></span>
        <div class="col-xs-1">
            <?= form_radio([
                'name'    => 'stock_location_default',
                'value'   => $locationId,
                'checked' => (bool) $locationData['is_default'],
                'class'   => 'stock_location_default'
            ]) ?>
        </div>
        <?= form_label(lang('Config.stock_location') . " $i", "stock_location_$i", ['class' => 'required control-label col-xs-2']) ?>
        <div class="col-xs-2">
            <?php $formData = [
                'name'  => "stock_location[$locationId]",
                'id'    => "stock_location[$locationId]",
                'class' => 'stock_location valid_chars form-control input-sm required',
                'value' => $locationName
            ];
            $locationData['deleted'] && $formData['disabled'] = 'disabled';
            echo form_input($formData);
            ?>
        </div>
        <span class="add_stock_location glyphicon glyphicon-plus" style="padding-top: 0.5em;"></span>
        <span>&nbsp;&nbsp;</span>
        <span class="remove_stock_location glyphicon glyphicon-minus" style="padding-top: 0.5em;"></span>
        <div class="clearfix"></div>
    </div>

<?php } ?>

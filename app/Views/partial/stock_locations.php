<?php
/**
 * @var array $stock_locations
 */
?>
<?php
$i = 0;

foreach($stock_locations as $location => $location_data)
{
    $location_id = $location_data['location_id'];
    $location_name = $location_data['location_name'];
    ++$i;
?>
    <div class="form-group form-group-sm" style="<?= $location_data['deleted'] ? 'display:none;' : 'display:block;' ?>">
        <?= form_label(lang('Config.stock_location') . " $i", "stock_location_$i", ['class' => 'required control-label col-xs-2']) ?>
        <div class='col-xs-2'>
            <?php $form_data = [
                    'name' => "stock_location[$location_id]",
                    'id' => "stock_location[$location_id]",
                    'class' => 'stock_location valid_chars form-control input-sm required',
                    'value' => $location_name
                ];
                $location_data['deleted'] && $form_data['disabled'] = 'disabled';
                echo form_input($form_data);
            ?>
        </div>
        <span class="add_stock_location glyphicon glyphicon-plus" style="padding-top: 0.5em;"></span>
        <span>&nbsp;&nbsp;</span>
        <span class="remove_stock_location glyphicon glyphicon-minus" style="padding-top: 0.5em;"></span>
    </div>
<?php
}
?>

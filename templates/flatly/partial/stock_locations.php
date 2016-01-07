<?php $i = 0; ?>
<?php foreach($stock_locations as $location => $location_data ) { ?>
<?php $location_id = $location_data['location_id']; ?>
<?php $location_name = $location_data['location_name']; ?>
<div class="field_row clearfix" style="<?php echo $location_data['deleted'] ? 'display:none;' : 'display:block;' ?>">    
<?php echo form_label($this->lang->line('config_stock_location').' ' .++$i. ':', 'stock_location_'.$i ,array('class'=>'required wide')); ?>
    <div class='form_field'>
    <?php $form_data = array(
        'name'=>'stock_location_'.$location_id,
        'id'=>'stock_location_'.$location_id,
    	'class'=>'stock_location valid_chars required',
        'value'=>$location_name); 
    	$location_data['deleted'] && $form_data['disabled'] = 'disabled';
    	echo form_input($form_data);
    ?>
    </div>
    <img class="add_stock_location" src="<?php echo base_url('images/plus.png'); ?>" />
    <img class="remove_stock_location" src="<?php echo base_url('images/minus.png'); ?>" />
</div>
<?php } ?>

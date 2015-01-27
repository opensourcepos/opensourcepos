<div id="stock_locations">
	<?php $i = 0; ?>
	<?php foreach($stock_locations as $location => $location_data ) { ?>
	<?php $location_id = $location_data['location_id']; ?>
	<?php $location_name = $location_data['location_name']; ?>
	<?php $hidden = $location_data['deleted']; ?>
	<div class="field_row clearfix" style="<? echo $hidden ? 'visibility:hidden;display:none;' : 'display:block' ?>">    
	<?php echo form_label($this->lang->line('config_stock_location').' ' .++$i. ':', 'stock_location_'.$i ,array('class'=>'required wide')); ?>
	    <div class='form_field'>
	    <?php echo form_input(array(
	        'name'=>'stock_location_'.$location_id,
	        'id'=>'stock_location_'.$location_id,
	    	'class'=>'stock_location_'.$location_id,
	        'value'=>$location_name)); ?>
	    </div>
	    <img class="add_stock_location" src="<?php echo base_url('images/plus.png'); ?>" />
	    <img class="remove_stock_location" src="<?php echo base_url('images/minus.png'); ?>" />
	</div>
	<?php } ?>
</div>visibility
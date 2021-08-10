<?php
$i = 0;

foreach ($stock_locations as $location => $location_data) {
	$location_id = $location_data['location_id'];
	$location_name = $location_data['location_name'];
	++$i;
?>
	<div class="form-group form-group-sm" style="<?= $location_data['deleted'] ? 'display:none;' : 'display:block;' ?>">
		<?= form_label($this->lang->line('config_stock_location') . ' ' . $i, 'stock_location_' . $i, array('class' => 'required control-label col-xs-2')); ?>
		<div class='col-xs-2'>
			<?php $form_data = array(
				'name' => "stock_location[$location_id]",
				'id' => "stock_location[$location_id]",
				'class' => 'stock_location valid_chars form-control input-sm required',
				'value' => $location_name
			);
			$location_data['deleted'] && $form_data['disabled'] = 'disabled';
			echo form_input($form_data);
			?>
		</div>
		<i class="add_stock_location bi bi-plus pt-2"></i>
		<span>&nbsp;&nbsp;</span>
		<i class="remove_stock_location bi bi-dash pt-2"></i>
	</div>
<?php
}
?>
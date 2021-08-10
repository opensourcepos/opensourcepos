<?php
$i = 0;
foreach ($dinner_tables as $dinner_tables => $table) {
	$dinner_table_id = $table['dinner_table_id'];
	$dinner_table_name = $table['name'];
	++$i;
?>

	<div class="form-group" style="<?= $table['deleted'] ? 'display:none;' : 'display:block;' ?>">
		<?= form_label($this->lang->line('config_dinner_table') . ' ' . $i, 'dinner_table_' . $i, array('class' => 'required control-label col-xs-2')); ?>
		<div class="col-xs-2">
			<?php $form_data = array(
				'name' => 'dinner_table_' . $dinner_table_id,
				'id' => 'dinner_table_' . $dinner_table_id,
				'class' => 'dinner_table valid_chars form-control input required',
				'value' => $dinner_table_name
			);
			$table['deleted'] && $form_data['disabled'] = 'disabled';
			echo form_input($form_data);
			?>
		</div>
		<i class="add_dinner_table bi bi-plus"></i>
		<span>&nbsp;&nbsp;</span>
		<i class="remove_dinner_table bi bi-dash"></i>
	</div>

<?php } ?>
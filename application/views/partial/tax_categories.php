<?php
$i = 0;

foreach($tax_categories as $key => $category)
{
	$tax_category_id = $category['tax_category_id'];
	$tax_category = $category['tax_category'];
	$default_tax_rate = $category['default_tax_rate'];
	$tax_group_sequence = $category['tax_group_sequence'];
	++$i;
	?>
	<div class="form-group form-group-sm" style="display:block;">
		<?php echo form_label($this->lang->line('taxes_tax_category') . ' ' . $i, 'tax_category_' . $i, array('class' => 'control-label col-xs-2')); ?>
		<div class='col-xs-3'>
			<?php $form_data = array(
				'name' => 'tax_category_' . $i,
				'id' => 'tax_category_' . $i,
				'class' => 'valid_chars form-control input-sm required',
				'placeholder' => $this->lang->line('taxes_tax_category_name'),
				'value' => $tax_category
			);
			echo form_input($form_data);
			?>
		</div>
		<div class='col-xs-2 input-group'  style='display:inline-table;float:left;'>
			<?php $form_data = array(
				'name' => 'default_tax_rate_' . $i,
				'id' => 'default_tax_rate_' . $i,
				'class' => 'valid_chars form-control input-sm',
				'placeholder' => $this->lang->line('taxes_default_tax_rate'),
				'value' => $default_tax_rate
			);
			echo form_input($form_data);
			?>
			<span class="input-group-addon input-sm">%</span>
		</div>
		<div class='col-xs-2'>
			<?php $form_data = array(
				'name' => 'tax_group_sequence_' . $i,
				'id' => 'tax_group_sequence_' . $i,
				'class' => 'valid_chars form-control input-sm',
				'placeholder' => $this->lang->line('taxes_sequence'),
				'value' => $tax_group_sequence
			);
			echo form_input($form_data);
			?>
		</div>
		<span class="add_tax_category glyphicon glyphicon-plus" style="padding-top: 0.5em;"></span>
		<span>&nbsp;&nbsp;</span>
		<span class="remove_tax_category glyphicon glyphicon-minus" style="padding-top: 0.5em;"></span>
		<?php echo form_hidden('tax_category_id_' . $i, $tax_category_id); ?>
	</div>
	<?php
}
if($i == 0)
{
	?>
	<div class="form-group form-group-sm" style="display:block;">
		<?php echo form_label($this->lang->line('taxes_tax_category') . ' 1', 'tax_category_1', array('class' => 'control-label col-xs-2')); ?>
		<div class='col-xs-3'>
			<?php $form_data = array(
				'name' => 'tax_category_1',
				'id' => 'tax_category_1',
				'class' => 'valid_chars form-control input-sm required',
				'placeholder' => $this->lang->line('taxes_tax_category_name'),
				'value' => ''
			);
			echo form_input($form_data);
			?>
		</div>
		<div class='col-xs-2 input-group' style='display:inline-table;float:left;'>
			<?php $form_data = array(
				'name' => 'default_tax_rate_1',
				'id' => 'default_tax_rate_1',
				'class' => 'valid_chars form-control',
				'placeholder' => $this->lang->line('taxes_default_tax_rate'),
				'value' => ''
			);
			echo form_input($form_data);
			?>
			<span class="input-group-addon input-sm">%</span>
		</div>
		<div class='col-xs-2'>
			<?php $form_data = array(
				'name' => 'tax_group_sequence_1',
				'id' => 'tax_group_sequence_1',
				'class' => 'valid_chars form-control input-sm',
				'placeholder' => $this->lang->line('taxes_sequence'),
				'value' => ''
			);
			echo form_input($form_data);
			?>
		</div>
		<span class="add_tax_category glyphicon glyphicon-plus" style="padding-top: 0.5em;"></span>
		<span>&nbsp;&nbsp;</span>
		<span class="remove_tax_category glyphicon glyphicon-minus" style="padding-top: 0.5em;"></span>
		<?php echo form_hidden('tax_category_id_1', '-1') ?>
	</div>
	<?php
}
?>

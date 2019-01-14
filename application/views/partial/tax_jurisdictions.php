<?php
$i = 0;

foreach($tax_jurisdictions as $tax_jurisdiction => $jurisdiction)
{
	$jurisdiction_id = $jurisdiction['jurisdiction_id'];
	$jurisdiction_name = $jurisdiction['jurisdiction_name'];
	$tax_group = $jurisdiction['tax_group'];
	$reporting_authority = $jurisdiction['reporting_authority'];
	$tax_type = $jurisdiction['tax_type'];
	$tax_group_sequence = $jurisdiction['tax_group_sequence'];
	$cascade_sequence = $jurisdiction['cascade_sequence'];
	++$i;
?>
	<div class="form-group form-group-sm" style="display:block;" >
		<?php echo form_label($this->lang->line('taxes_tax_jurisdiction') . ' ' . $i, 'jurisdiction_name_' . $i, array('class'=>'control-label col-xs-2')); ?>
		<div class='col-xs-2'>
			<?php $form_data = array(
				'name'=>'jurisdiction_name[]',
				'id'=>'jurisdiction_name_' . $i,
				'class'=>'valid_chars form-control input-sm',
				'placeholder'=>$this->lang->line('taxes_jurisdiction_name'),
				'value'=>$jurisdiction_name
				);
				echo form_input($form_data);
			?>
		</div>

		<div class='col-xs-1'>
			<?php $form_data = array(
				'name'=>'tax_group[]',
				'class'=>'valid_chars form-control input-sm',
				'placeholder'=>$this->lang->line('taxes_tax_group'),
				'value'=>$tax_group
			);
			echo form_input($form_data);
			?>
		</div>

		<div class='col-xs-2'>
			<?php echo form_dropdown('tax_type[]' . $i, $tax_types, $tax_type, array('class'=>'form-control'));	?>
		</div>

		<div class='col-xs-2'>
			<?php $form_data = array(
				'name'=>'reporting_authority[]',
				'class'=>'valid_chars form-control input-sm',
				'placeholder'=>$this->lang->line('taxes_reporting_authority'),
				'value'=>$reporting_authority
			);
			echo form_input($form_data);
			?>
		</div>

		<div class='col-xs-1'>
			<?php $form_data = array(
				'name'=>'tax_group_sequence[]',
				'class'=>'valid_chars form-control input-sm',
				'placeholder' => $this->lang->line('taxes_sequence'),
				'value'=>$tax_group_sequence
			);
			echo form_input($form_data);
			?>
		</div>

		<div class='col-xs-1'>
			<?php $form_data = array(
				'name'=>'cascade_sequence[]',
				'class'=>'valid_chars form-control input-sm',
				'placeholder'=>$this->lang->line('taxes_cascade_sequence'),
				'value'=>$cascade_sequence
			);
			echo form_input($form_data);
			?>
		</div>
		<span class="add_tax_jurisdiction glyphicon glyphicon-plus" style="padding-top: 0.5em;"></span>
		<span>&nbsp;&nbsp;</span>
		<span class="remove_tax_jurisdiction glyphicon glyphicon-minus" style="padding-top: 0.5em;"></span>
		<?php echo form_hidden('jurisdiction_id[]', $jurisdiction_id); ?>
	</div>
<?php
}
?>

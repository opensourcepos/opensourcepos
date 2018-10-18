<?php
$i = 0;

foreach($tax_jurisdictions as $tax_jurisdiction => $jurisdiction)
{
	$jurisdiction_id = $jurisdiction['jurisdiction_id'];
	$jurisdiction_name = $jurisdiction['jurisdiction_name'];
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
				'name'=>'jurisdiction_name_' . $i,
				'id'=>'jurisdiction_name_' . $i,
				'class'=>'valid_chars form-control input-sm required',
				'placeholder'=>$this->lang->line('taxes_jurisdiction_name'),
				'value'=>$jurisdiction_name
				);
				echo form_input($form_data);
			?>
		</div>

		<div class='col-xs-2'>
			<?php echo form_dropdown('tax_type_' . $i, $tax_types, $tax_type, array('class'=>'form-control'));	?>
		</div>

        <div class='col-xs-2'>
			<?php $form_data = array(
				'name'=>'reporting_authority_' . $i,
				'id'=>'reporting_authority_' . $i,
				'class'=>'valid_chars form-control input-sm',
				'placeholder'=>$this->lang->line('taxes_reporting_authority'),
				'value'=>$reporting_authority
			);
			echo form_input($form_data);
			?>
        </div>

		<div class='col-xs-2'>
			<?php $form_data = array(
				'name'=>'tax_group_sequence_' . $i,
				'id'=>'tax_group_sequence_' . $i,
				'class'=>'valid_chars form-control input-sm',
				'placeholder' => $this->lang->line('taxes_sequence'),
				'value'=>$tax_group_sequence
			);
			echo form_input($form_data);
			?>
		</div>

		<div class='col-xs-1'>
			<?php $form_data = array(
				'name'=>'cascade_sequence_' . $i,
				'id'=>'cascade_sequence_' . $i,
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
		<?php echo form_hidden('jurisdiction_id_' . $i, $jurisdiction_id); ?>
	</div>
<?php
}
if ($i == 0)
{
?>
	<div class="form-group form-group-sm" style="display:block;" >
		<?php echo form_label($this->lang->line('taxes_tax_jurisdiction') . ' 1', 'jurisdiction_name_1', array('class'=>'control-label col-xs-2')); ?>
		<div class='col-xs-2'>
			<?php $form_data = array(
				'name'=>'jurisdiction_name_1',
				'id'=>'jurisdiction_name_1',
				'class'=>'valid_chars form-control input-sm required',
				'placeholder'=>$this->lang->line('taxes_jurisdiction_name'),
				'value'=>''
			);
			echo form_input($form_data);
			?>
		</div>
		<div class='col-xs-2'>
			<?php echo form_dropdown('tax_type_1', $tax_types, $default_tax_type, array('class'=>'form-control'));	?>
		</div>
		<div class='col-xs-2'>
			<?php $form_data = array(
				'name'=>'reporting_authority_1',
				'id'=>'reporting_authority_1',
				'class'=>'valid_chars form-control input-sm',
				'placeholder'=>$this->lang->line('taxes_reporting_authority'),
				'value'=>''
			);
			echo form_input($form_data);
			?>
		</div>
		<div class='col-xs-2'>
			<?php $form_data = array(
				'name'=>'tax_group_sequence_1',
				'id'=>'tax_group_sequence_1',
				'class'=>'valid_chars form-control input-sm',
				'placeholder' => $this->lang->line('taxes_sequence'),
				'value'=>''
			);
			echo form_input($form_data);
			?>
		</div>
		<div class='col-xs-1'>
			<?php $form_data = array(
				'name'=>'cascade_sequence_1',
				'id'=>'cascade_sequence_1',
				'class'=>'valid_chars form-control input-sm',
				'placeholder'=>$this->lang->line('taxes_cascade_sequence'),
				'value'=>''
			);
			echo form_input($form_data);
			?>
		</div>
		<span class="add_tax_jurisdiction glyphicon glyphicon-plus" style="padding-top: 0.5em;"></span>
		<span>&nbsp;&nbsp;</span>
		<span class="remove_tax_jurisdiction glyphicon glyphicon-minus" style="padding-top: 0.5em;"></span>
		<?php echo form_hidden('jurisdiction_id_1', -1); ?>
	</div>
<?php
}
?>

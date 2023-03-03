<?php
/**
 * @var array $receiving_info
 * @var string $selected_supplier_name
 * @var int $selected_supplier_id
 * @var array $employees
 * @var string $controller_name
 */
?>
<div id="required_fields_message"><?php echo lang('Common.fields_required_message') ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open("receivings/save/".$receiving_info['receiving_id'], ['id' => 'receivings_edit_form', 'class' => 'form-horizontal']) ?>
	<fieldset id="receiving_basic_info">
		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Receivings.receipt_number'), 'supplier', ['class' => 'control-label col-xs-3']) ?>
			<?php echo anchor('receivings/receipt/' . $receiving_info['receiving_id'], 'RECV ' . $receiving_info['receiving_id'], ['target' => '_blank', 'class' => 'control-label col-xs-8', "style" => "text-align:left"]) ?>
		</div>
		
		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Receivings.date'), 'date', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<?php echo form_input ([
					'name'	=> 'date',
					'value'	=> to_datetime(strtotime($receiving_info['receiving_time'])),
					'id'	=> 'datetime',
					'class'	=> 'datetime form-control input-sm',
					'readonly' => 'readonly'
				]) ?>
			</div>
		</div>
		
		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Receivings.supplier'), 'supplier', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<?php echo form_input (['name' => 'supplier_name', 'value' => esc($selected_supplier_name), 'id' => 'supplier_name', 'class' => 'form-control input-sm']) ?>
				<?php echo form_hidden('supplier_id', $selected_supplier_id) ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Receivings.reference'), 'reference', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<?php echo form_input (['name' => 'reference', 'value' => esc($receiving_info['reference']), 'id' => 'reference', 'class' => 'form-control input-sm']) ?>
			</div>
		</div>
		
		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Receivings.employee'), 'employee', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<?php echo form_dropdown('employee_id', esc($employees), $receiving_info['employee_id'], 'id="employee_id" class="form-control"') ?>
			</div>
		</div>
		
		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Receivings.comments'), 'comment', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<?php echo form_textarea (['name' => 'comment','value' => esc($receiving_info['comment']), 'id' => 'comment', 'class' => 'form-control input-sm']) ?>
			</div>
		</div>
	</fieldset>
<?php echo form_close() ?>
		
<script type="text/javascript">
$(document).ready(function()
{
	<?php echo view('partial/datepicker_locale') ?>

    $('#datetime').datetimepicker(pickerconfig);

	var fill_value = function(event, ui) {
		event.preventDefault();
		$("input[name='supplier_id']").val(ui.item.value);
		$("input[name='supplier_name']").val(ui.item.label);
	};

	$('#supplier_name').autocomplete({
		source: "<?php echo 'suppliers/suggest' ?>",
		minChars: 0,
		delay: 15, 
		cacheLength: 1,
		appendTo: '.modal-content',
		select: fill_value,
		focus: fill_value
	});

	$('button#delete').click(function()
	{
		dialog_support.hide();
		table_support.do_delete("<?php echo esc($controller_name) ?>", <?php echo $receiving_info['receiving_id'] ?>);
	});

	$('#receivings_edit_form').validate($.extend({
		submitHandler: function(form) {
			$(form).ajaxSubmit({
				success: function(response)
				{
					dialog_support.hide();
					table_support.handle_submit("<?php echo esc($controller_name) ?>", response);
				},
				dataType: 'json'
			});
		}
	}, form_support.error));
});
</script>

<div id="required_fields_message"><?= $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?= form_open("receivings/save/" . $receiving_info['receiving_id'], array('id' => 'receivings_edit_form', 'class' => 'form-horizontal')); ?>
<fieldset id="receiving_basic_info">
	<div class="form-group form-group-sm">
		<?= form_label($this->lang->line('receivings_receipt_number'), 'supplier', array('class' => 'control-label col-xs-3')); ?>
		<?= anchor('receivings/receipt/' . $receiving_info['receiving_id'], 'RECV ' . $receiving_info['receiving_id'], array('target' => '_blank', 'class' => 'control-label col-xs-8', "style" => "text-align:left")); ?>
	</div>

	<div class="form-group form-group-sm">
		<?= form_label($this->lang->line('receivings_date'), 'date', array('class' => 'control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<?= form_input(array(
				'name'	=> 'date',
				'value'	=> to_datetime(strtotime($receiving_info['receiving_time'])),
				'id'	=> 'datetime',
				'class'	=> 'datetime form-control input-sm',
				'readonly' => 'readonly'
			));
			?>
		</div>
	</div>

	<div class="form-group form-group-sm">
		<?= form_label($this->lang->line('receivings_supplier'), 'supplier', array('class' => 'control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<?= form_input(array('name' => 'supplier_name', 'value' => $selected_supplier_name, 'id' => 'supplier_name', 'class' => 'form-control input-sm')); ?>
			<?= form_hidden('supplier_id', $selected_supplier_id); ?>
		</div>
	</div>

	<div class="form-group form-group-sm">
		<?= form_label($this->lang->line('receivings_reference'), 'reference', array('class' => 'control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<?= form_input(array('name' => 'reference', 'value' => $receiving_info['reference'], 'id' => 'reference', 'class' => 'form-control input-sm')); ?>
		</div>
	</div>

	<div class="form-group form-group-sm">
		<?= form_label($this->lang->line('receivings_employee'), 'employee', array('class' => 'control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<?= form_dropdown('employee_id', $employees, $receiving_info['employee_id'], 'id="employee_id" class="form-control"'); ?>
		</div>
	</div>

	<div class="form-group form-group-sm">
		<?= form_label($this->lang->line('receivings_comments'), 'comment', array('class' => 'control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<?= form_textarea(array('name' => 'comment', 'value' => $receiving_info['comment'], 'id' => 'comment', 'class' => 'form-control input-sm')); ?>
		</div>
	</div>
</fieldset>
<?= form_close(); ?>

<script type="text/javascript">
	$(document).ready(function() {
		<?php $this->load->view('partial/datepicker_locale'); ?>

		$('#datetime').datetimepicker(pickerconfig);

		var fill_value = function(event, ui) {
			event.preventDefault();
			$("input[name='supplier_id']").val(ui.item.value);
			$("input[name='supplier_name']").val(ui.item.label);
		};

		$('#supplier_name').autocomplete({
			source: "<?= site_url('suppliers/suggest'); ?>",
			minChars: 0,
			delay: 15,
			cacheLength: 1,
			appendTo: '.modal-content',
			select: fill_value,
			focus: fill_value
		});

		$('button#delete').click(function() {
			dialog_support.hide();
			table_support.do_delete("<?= site_url($controller_name); ?>", <?= $receiving_info['receiving_id']; ?>);
		});

		$('#receivings_edit_form').validate($.extend({
			submitHandler: function(form) {
				$(form).ajaxSubmit({
					success: function(response) {
						dialog_support.hide();
						table_support.handle_submit("<?= site_url($controller_name); ?>", response);
					},
					dataType: 'json'
				});
			}
		}, form_support.error));
	});
</script>
<?php echo form_open('config/save_invoice/', array('id' => 'invoice_config_form', 'class' => 'form-horizontal')); ?>
	<div id="config_wrapper">
		<fieldset id="config_info">
			<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
			<ul id="invoice_error_message_box" class="error_message_box"></ul>

			<div class="form-group form-group-sm">	
				<?php echo form_label($this->lang->line('config_invoice_enable'), 'invoice_enable', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-1'>
					<?php echo form_checkbox(array(
						'name' => 'invoice_enable',
						'value' => 'invoice_enable',
						'id' => 'invoice_enable',
						'checked' => $this->config->item('invoice_enable')));?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_invoice_type'), 'invoice_type', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-3'>
					<?php echo form_dropdown('invoice_type', $invoice_type_options, $this->config->item('invoice_type'), array('class' => 'form-control input-sm')); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_recv_invoice_format'), 'recv_invoice_format', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_input(array(
						'name' => 'recv_invoice_format',
						'id' => 'recv_invoice_format',
						'class' => 'form-control input-sm',
						'value' => $this->config->item('recv_invoice_format'))); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">	
				<?php echo form_label($this->lang->line('config_invoice_default_comments'), 'invoice_default_comments', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-5'>
					<?php echo form_textarea(array(
						'name' => 'invoice_default_comments',
						'id' => 'invoice_default_comments',
						'class' => 'form-control input-sm',
						'value' => $this->config->item('invoice_default_comments')));?>
				</div>
			</div>

			<div class="form-group form-group-sm">	
				<?php echo form_label($this->lang->line('config_invoice_email_message'), 'invoice_email_message', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-5'>
					<?php echo form_textarea(array(
						'name' => 'invoice_email_message',
						'id' => 'invoice_email_message',
						'class' => 'form-control input-sm',
						'value' => $this->config->item('invoice_email_message')));?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_line_sequence'), 'line_sequence', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_dropdown('line_sequence', $line_sequence_options, $this->config->item('line_sequence'), array('class' => 'form-control input-sm')); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_sales_invoice_format'), 'sales_invoice_format', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_input(array(
						'name' => 'sales_invoice_format',
						'id' => 'sales_invoice_format',
						'class' => 'form-control input-sm',
						'value' => $this->config->item('sales_invoice_format'))); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_last_used_invoice_number'), 'last_used_invoice_number', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_input(array(
						'type' => 'number',
						'name' => 'last_used_invoice_number',
						'id' => 'last_used_invoice_number',
						'class' => 'form-control input-sm required',
						'value'=>$this->config->item('last_used_invoice_number'))); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_sales_quote_format'), 'sales_quote_format', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_input(array(
						'name' => 'sales_quote_format',
						'id' => 'sales_quote_format',
						'class' => 'form-control input-sm',
						'value' => $this->config->item('sales_quote_format'))); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_last_used_quote_number'), 'last_used_quote_number', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_input(array(
						'type' => 'number',
						'name' => 'last_used_quote_number',
						'id' => 'last_used_quote_number',
						'class' => 'form-control input-sm required',
						'value'=>$this->config->item('last_used_quote_number'))); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_quote_default_comments'), 'quote_default_comments', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-5'>
					<?php echo form_textarea(array(
						'name' => 'quote_default_comments',
						'id' => 'quote_default_comments',
						'class' => 'form-control input-sm',
						'value' => $this->config->item('quote_default_comments')));?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_work_order_enable'), 'work_order_enable', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-1'>
					<?php echo form_checkbox(array(
						'name' => 'work_order_enable',
						'value' => 'work_order_enable',
						'id' => 'work_order_enable',
						'checked' => $this->config->item('work_order_enable')));?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_work_order_format'), 'work_order_format', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_input(array(
						'name' => 'work_order_format',
						'id' => 'work_order_format',
						'class' => 'form-control input-sm',
						'value' => $this->config->item('work_order_format'))); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_last_used_work_order_number'), 'last_used_work_order_number', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_input(array(
						'type' => 'number',
						'name' => 'last_used_work_order_number',
						'id' => 'last_used_work_order_number',
						'class' => 'form-control input-sm required',
						'value'=>$this->config->item('last_used_work_order_number'))); ?>
				</div>
			</div>

			<?php echo form_submit(array(
				'name' => 'submit_invoice',
				'id' => 'submit_invoice',
				'value' => $this->lang->line('common_submit'),
				'class' => 'btn btn-primary btn-sm pull-right'));?>
		</fieldset>
	</div>
<?php echo form_close(); ?>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{
	var enable_disable_invoice_enable = (function() {
		var invoice_enabled = $("#invoice_enable").is(":checked");
		var work_order_enabled = $("#work_order_enable").is(":checked");
		$("#sales_invoice_format, #recv_invoice_format, #invoice_default_comments, #invoice_email_message, select[name='invoice_type'], #sales_quote_format, select[name='line_sequence'], #last_used_invoice_number, #last_used_quote_number, #quote_default_comments, #work_order_enable, #work_order_format, #last_used_work_order_number").prop("disabled", !invoice_enabled);
		if(invoice_enabled) {
			$("#work_order_format, #last_used_work_order_number").prop("disabled", !work_order_enabled);
		} else {
			$("#work_order_enable").attr('checked', false);
		}
		return arguments.callee;
	})();

	var enable_disable_work_order_enable = (function() {
		var work_order_enabled = $("#work_order_enable").is(":checked");
		var invoice_enabled = $("#invoice_enable").is(":checked");
		if(invoice_enabled) {
			$("#work_order_format, #last_used_work_order_number").prop("disabled", !work_order_enabled);
		}
		return arguments.callee;
	})();

	$("#invoice_enable").change(enable_disable_invoice_enable);

	$("#work_order_enable").change(enable_disable_work_order_enable);

	$("#invoice_config_form").validate($.extend(form_support.handler, {

		errorLabelContainer: "#invoice_error_message_box",

		submitHandler: function(form) {
			$(form).ajaxSubmit({
				beforeSerialize: function(arr, $form, options) {
					$("#sales_invoice_format, #sales_quote_format, #recv_invoice_format, #invoice_default_comments, #invoice_email_message, #last_used_invoice_number, #last_used_quote_number, #quote_default_comments, #work_order_enable, #work_order_format, #last_used_work_order_number").prop("disabled", false);
					return true;
				},
				success: function(response) {
					$.notify( { message: response.message }, { type: response.success ? 'success' : 'danger'} )
					// set back disabled state
					enable_disable_invoice_enable();
					enable_disable_work_order_enable();
				},
				dataType: 'json'
			});
		}
	}));
});
</script>

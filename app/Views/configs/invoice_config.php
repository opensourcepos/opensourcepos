<?php
/**
 * @var array $invoice_type_options
 * @var array $line_sequence_options
 */
?>
<?php echo form_open('config/save_invoice/', ['id' => 'invoice_config_form', 'class' => 'form-horizontal']) ?>
	<div id="config_wrapper">
		<fieldset id="config_info">
			<div id="required_fields_message"><?php echo lang('Common.fields_required_message') ?></div>
			<ul id="invoice_error_message_box" class="error_message_box"></ul>

			<div class="form-group form-group-sm">	
				<?php echo form_label(lang('Config.invoice_enable'), 'invoice_enable', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-1'>
					<?php echo form_checkbox ([
						'name' => 'invoice_enable',
						'value' => 'invoice_enable',
						'id' => 'invoice_enable',
						'checked' => config('OSPOS')->settings['invoice_enable']
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.invoice_type'), 'invoice_type', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-3'>
					<?php echo form_dropdown('invoice_type', esc($invoice_type_options, 'attr'), esc(config('OSPOS')->settings['invoice_type'], 'attr'), ['class' => 'form-control input-sm']) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.recv_invoice_format'), 'recv_invoice_format', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?php echo form_input ([
						'name' => 'recv_invoice_format',
						'id' => 'recv_invoice_format',
						'class' => 'form-control input-sm',
						'value' => esc(config('OSPOS')->settings['recv_invoice_format'], 'attr')
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">	
				<?php echo form_label(lang('Config.invoice_default_comments'), 'invoice_default_comments', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-5'>
					<?php echo form_textarea ([
						'name' => 'invoice_default_comments',
						'id' => 'invoice_default_comments',
						'class' => 'form-control input-sm',
						'value' => esc(config('OSPOS')->settings['invoice_default_comments'], 'attr')
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">	
				<?php echo form_label(lang('Config.invoice_email_message'), 'invoice_email_message', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-5'>
					<?php echo form_textarea ([
						'name' => 'invoice_email_message',
						'id' => 'invoice_email_message',
						'class' => 'form-control input-sm',
						'value' => esc(config('OSPOS')->settings['invoice_email_message'], 'attr')
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.line_sequence'), 'line_sequence', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?php echo form_dropdown('line_sequence', esc($line_sequence_options, 'attr'), esc(config('OSPOS')->settings['line_sequence'], 'attr'), ['class' => 'form-control input-sm']) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.sales_invoice_format'), 'sales_invoice_format', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?php echo form_input ([
						'name' => 'sales_invoice_format',
						'id' => 'sales_invoice_format',
						'class' => 'form-control input-sm',
						'value' => esc(config('OSPOS')->settings['sales_invoice_format'], 'attr')
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.last_used_invoice_number'), 'last_used_invoice_number', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?php echo form_input ([
						'type' => 'number',
						'name' => 'last_used_invoice_number',
						'id' => 'last_used_invoice_number',
						'class' => 'form-control input-sm required',
						'value' => config('OSPOS')->settings['last_used_invoice_number']
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.sales_quote_format'), 'sales_quote_format', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?php echo form_input ([
						'name' => 'sales_quote_format',
						'id' => 'sales_quote_format',
						'class' => 'form-control input-sm',
						'value' => esc(config('OSPOS')->settings['sales_quote_format'], 'attr')
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.last_used_quote_number'), 'last_used_quote_number', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?php echo form_input ([
						'type' => 'number',
						'name' => 'last_used_quote_number',
						'id' => 'last_used_quote_number',
						'class' => 'form-control input-sm required',
						'value'=>config('OSPOS')->settings['last_used_quote_number']
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.quote_default_comments'), 'quote_default_comments', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-5'>
					<?php echo form_textarea ([
						'name' => 'quote_default_comments',
						'id' => 'quote_default_comments',
						'class' => 'form-control input-sm',
						'value' => esc(config('OSPOS')->settings['quote_default_comments'], 'attr')
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.work_order_enable'), 'work_order_enable', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-1'>
					<?php echo form_checkbox ([
						'name' => 'work_order_enable',
						'value' => 'work_order_enable',
						'id' => 'work_order_enable',
						'checked' => config('OSPOS')->settings['work_order_enable']
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.work_order_format'), 'work_order_format', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?php echo form_input ([
						'name' => 'work_order_format',
						'id' => 'work_order_format',
						'class' => 'form-control input-sm',
						'value' => esc(config('OSPOS')->settings['work_order_format'], 'attr')
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.last_used_work_order_number'), 'last_used_work_order_number', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?php echo form_input ([
						'type' => 'number',
						'name' => 'last_used_work_order_number',
						'id' => 'last_used_work_order_number',
						'class' => 'form-control input-sm required',
						'value' => config('OSPOS')->settings['last_used_work_order_number']
					]) ?>
				</div>
			</div>

			<?php echo form_submit ([
				'name' => 'submit_invoice',
				'id' => 'submit_invoice',
				'value' => lang('Common.submit'),
				'class' => 'btn btn-primary btn-sm pull-right']) ?>
		</fieldset>
	</div>
<?php echo form_close() ?>

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

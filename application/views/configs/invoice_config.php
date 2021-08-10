<?= form_open('config/save_invoice/', array('id' => 'invoice_config_form', 'class' => 'form-horizontal')); ?>

<?php
$title_invoice['config_title'] = $this->lang->line('config_invoice_configuration');
$this->load->view('configs/config_header', $title_invoice);
?>

<ul id="invoice_error_message_box" class="error_message_box"></ul>

<div class="form-check form-switch mb-3">
	<input class="form-check-input" name="invoice_enable" type="checkbox" id="invoice-enable" checked="<?= $this->config->item('invoice_enable'); ?>">
	<label class="form-check-label" for="invoice-enable"><?= $this->lang->line('config_invoice_enable'); ?></label>
</div>

<div class="row">
	<div class="col-12 col-lg-6">
		<label for="invoice-type" class="form-label"><?= $this->lang->line('config_invoice_type'); ?></label>
		<div class="input-group mb-3">
			<label class="input-group-text"><i class="bi bi-file-richtext"></i></label>
			<?= form_dropdown('invoice_type', $invoice_type_options, $this->config->item('invoice_type'), array('class' => 'form-select', 'id' => 'invoice-type')); ?>
		</div>
	</div>

	<div class="col-12 col-lg-6">
		<label for="line-sequence" class="form-label"><?= $this->lang->line('config_line_sequence'); ?></label>
		<div class="input-group mb-3">
			<label class="input-group-text"><i class="bi bi-list-ul"></i></label>
			<?= form_dropdown('line_sequence', $line_sequence_options, $this->config->item('line_sequence'), array('class' => 'form-select', 'id' => 'line-sequence')); ?>
		</div>
	</div>

	<div class="col-12 col-lg-6">
		<label for="sales-invoice_format" class="form-label"><?= $this->lang->line('config_sales_invoice_format'); ?></label>
		<div class="input-group mb-3">
			<label class="input-group-text"><i class="bi bi-braces"></i></label>
			<input type="text" name="sales_invoice_format" class="form-control" id="sales-invoice-format" value="<?= $this->config->item('sales_invoice_format'); ?>">
		</div>
	</div>

	<div class="col-12 col-lg-6">
		<label for="recv-invoice-format" class="form-label"><?= $this->lang->line('config_recv_invoice_format'); ?></label>
		<div class="input-group mb-3">
			<label class="input-group-text"><i class="bi bi-braces"></i></label>
			<input type="text" name="recv_invoice_format" class="form-control" id="recv-invoice-format" value="<?= $this->config->item('recv_invoice_format'); ?>">
		</div>
	</div>

	<div class="col-12 col-lg-6">
		<label for="last-used-invoice-number" class="form-label"><?= $this->lang->line('config_last_used_invoice_number'); ?></label>
		<div class="input-group mb-3">
			<label class="input-group-text"><i class="bi bi-list-ol"></i></label>
			<input type="number" name="last_used_invoice_number" class="form-control" id="last-used-invoice-number" value="<?= $this->config->item('last_used_invoice_number'); ?>">
		</div>
	</div>
</div>


<label for="invoice-default-comments" class="form-label"><?= $this->lang->line('config_invoice_default_comments'); ?></label>
<div class="input-group mb-3">
	<span class="input-group-text"><i class="bi bi-chat-square-text"></i></span>
	<textarea class="form-control" name="invoice_default_comments" id="invoice-default-comments" rows="10"><?= $this->config->item('invoice_default_comments'); ?></textarea>
</div>

<label for="invoice-email-message" class="form-label"><?= $this->lang->line('config_invoice_email_message'); ?></label>
<div class="input-group mb-3">
	<span class="input-group-text"><i class="bi bi-card-text"></i></span>
	<textarea class="form-control" name="invoice_email_message" id="invoice-email-message" rows="10"><?= $this->config->item('invoice_email_message'); ?></textarea>
</div>

<div class="row">
	<div class="col-12 col-lg-6">
		<label for="sales-quote-format" class="form-label"><?= $this->lang->line('config_sales_quote_format'); ?></label>
		<div class="input-group mb-3">
			<label class="input-group-text"><i class="bi bi-braces"></i></label>
			<input type="text" name="sales_quote_format" class="form-control" id="sales-quote-format" value="<?= $this->config->item('sales_quote_format'); ?>">
		</div>
	</div>

	<div class="col-12 col-lg-6">
		<label for="last-used-quote-number" class="form-label"><?= $this->lang->line('config_last_used_quote_number'); ?></label>
		<div class="input-group mb-3">
			<label class="input-group-text"><i class="bi bi-list-ol"></i></label>
			<input type="number" name="last_used_quote_number" class="form-control" id="last-used-quote-number" value="<?= $this->config->item('last_used_quote_number'); ?>">
		</div>
	</div>
</div>

<label for="quote-default-comments" class="form-label"><?= $this->lang->line('config_quote_default_comments'); ?></label>
<div class="input-group mb-3">
	<span class="input-group-text"><i class="bi bi-chat-square-text"></i></span>
	<textarea class="form-control" name="quote_default_comments" id="quote-default-comments" rows="10"><?= $this->config->item('quote_default_comments'); ?></textarea>
</div>

<div class="form-check form-switch mb-3">
	<input class="form-check-input" name="work_order_enable" type="checkbox" id="work-order-enable" checked="<?= $this->config->item('work_order_enable'); ?>">
	<label class="form-check-label" for="work-order-enable"><?= $this->lang->line('config_work_order_enable'); ?></label>
</div>

<div class="row">
	<div class="col-12 col-lg-6">
		<label for="work-order-format" class="form-label"><?= $this->lang->line('config_work_order_format'); ?></label>
		<div class="input-group mb-3">
			<label class="input-group-text"><i class="bi bi-braces"></i></label>
			<input type="text" name="work_order_format" class="form-control" id="work-order-format" value="<?= $this->config->item('work_order_format'); ?>">
		</div>
	</div>

	<div class="col-12 col-lg-6">
		<label for="last-used-work-order-number" class="form-label"><?= $this->lang->line('config_last_used_work_order_number'); ?></label>
		<div class="input-group mb-3">
			<label class="input-group-text"><i class="bi bi-list-ol"></i></label>
			<input type="number" name="last_used_work_order_number" class="form-control" id="last-used-work-order-number" value="<?= $this->config->item('last_used_work_order_number'); ?>">
		</div>
	</div>
</div>

<div class="d-flex justify-content-end">
	<button class="btn btn-primary" name="submit_invoice"><?= $this->lang->line('common_submit'); ?></button>
</div>

<?= form_close(); ?>

<script type="text/javascript">
	//validation and submit handling
	$(document).ready(function() {
		var enable_disable_invoice_enable = (function() {
			var invoice_enabled = $("#invoice_enable").is(":checked");
			var work_order_enabled = $("#work_order_enable").is(":checked");
			$("#sales_invoice_format, #recv_invoice_format, #invoice_default_comments, #invoice_email_message, select[name='invoice_type'], #sales_quote_format, select[name='line_sequence'], #last_used_invoice_number, #last_used_quote_number, #quote_default_comments, #work_order_enable, #work_order_format, #last_used_work_order_number").prop("disabled", !invoice_enabled);
			if (invoice_enabled) {
				$("#work_order_format, #last_used_work_order_number").prop("disabled", !work_order_enabled);
			} else {
				$("#work_order_enable").attr('checked', false);
			}
			return arguments.callee;
		})();

		var enable_disable_work_order_enable = (function() {
			var work_order_enabled = $("#work_order_enable").is(":checked");
			var invoice_enabled = $("#invoice_enable").is(":checked");
			if (invoice_enabled) {
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
						$.notify({
							message: response.message
						}, {
							type: response.success ? 'success' : 'danger'
						})
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
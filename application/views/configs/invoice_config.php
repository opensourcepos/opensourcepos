<?php echo form_open('config/save_invoice/', array('id'=>'invoice_config_form', 'class'=>'form-horizontal')); ?>
	<div id="config_wrapper">
		<fieldset id="config_info">
			<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
			<ul id="receipt_error_message_box" class="error_message_box"></ul>

			<div class="form-group form-group-sm">	
				<?php echo form_label($this->lang->line('config_invoice_enable'), 'invoice_enable', array('class'=>'control-label col-xs-2')); ?>
				<div class='col-xs-1'>
					<?php echo form_checkbox(array(
						'name'=>'invoice_enable',
						'value'=>'invoice_enable',
						'id'=>'invoice_enable',
						'checked'=>$this->config->item('invoice_enable')));?>
				</div>
			</div>
			
			<div class="form-group form-group-sm">    
				<?php echo form_label($this->lang->line('config_sales_invoice_format'), 'sales_invoice_format', array('class'=>'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_input(array(
						'name'=>'sales_invoice_format',
						'id'=>'sales_invoice_format',
						'class'=>'form-control input-sm',
						'value'=>$this->config->item('sales_invoice_format'))); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">    
				<?php echo form_label($this->lang->line('config_recv_invoice_format'), 'recv_invoice_format', array('class'=>'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_input(array(
						'name'=>'recv_invoice_format',
						'id'=>'recv_invoice_format',
						'class'=>'form-control input-sm',
						'value'=>$this->config->item('recv_invoice_format'))); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">	
				<?php echo form_label($this->lang->line('config_use_invoice_template'), 'use_invoice_template', array('class'=>'control-label col-xs-2')); ?>
				<div class='col-xs-1'>
					<?php echo form_checkbox(array(
						'name'=>'use_invoice_template',
						'value'=>'use_invoice_template',
						'id'=>'use_invoice_template',
						'checked'=>$this->config->item('use_invoice_template')));?>
				</div>
			</div>

			<div class="form-group form-group-sm">	
				<?php echo form_label($this->lang->line('config_invoice_default_comments'), 'invoice_default_comments', array('class'=>'control-label col-xs-2')); ?>
				<div class='col-xs-5'>
					<?php echo form_textarea(array(
						'name'=>'invoice_default_comments',
						'id'=>'invoice_default_comments',
						'class'=>'form-control input-sm',
						'value'=>$this->config->item('invoice_default_comments')));?>
				</div>
			</div>

			<div class="form-group form-group-sm">	
				<?php echo form_label($this->lang->line('config_invoice_email_message'), 'invoice_email_message', array('class'=>'control-label col-xs-2')); ?>
				<div class='col-xs-5'>
					<?php echo form_textarea(array(
						'name'=>'invoice_email_message',
						'id'=>'invoice_email_message',
						'class'=>'form-control input-sm',
						'value'=>$this->config->item('invoice_email_message')));?>
				</div>
			</div>

			<?php echo form_submit(array(
				'name'=>'submit_form',
				'id'=>'submit_form',
				'value'=>$this->lang->line('common_submit'),
				'class'=>'btn btn-primary btn-sm pull-right'));?>
		</fieldset>
	</div>
<?php echo form_close(); ?>

<script type='text/javascript'>
//validation and submit handling
$(document).ready(function()
{
	var enable_disable_invoice_enable = (function() {
		var invoice_enable = $("#invoice_enable").is(":checked");
		$("#sales_invoice_format, #recv_invoice_format, #use_invoice_template").prop('disabled', !invoice_enable);
		return arguments.callee;
	})();
	$("#invoice_enable").change(enable_disable_invoice_enable);

	var enable_disable_use_invoice_template = (function() {
		var use_invoice_template = $("#use_invoice_template").is(":checked");
		$("#invoice_default_comments, #invoice_email_message").prop('disabled', !use_invoice_template);
		return arguments.callee;
	})();
	$("#use_invoice_template").change(enable_disable_use_invoice_template);
	
	$('#invoice_config_form').validate({
		submitHandler: function(form) {
			$(form).ajaxSubmit({
				beforeSerialize: function(arr, $form, options) {
					$("#sales_invoice_format, #recv_invoice_format, #use_invoice_template, #invoice_default_comments, #invoice_email_message").prop("disabled", false); 
					return true;
				},
				success: function(response) {
					if(response.success)
					{
						set_feedback(response.message, 'alert alert-dismissible alert-success', false);		
					}
					else
					{
						set_feedback(response.message, 'alert alert-dismissible alert-danger', true);		
					}
					// set back disabled state
					enable_disable_invoice_enable();
					enable_disable_use_invoice_template();
				},
				dataType:'json'
			});
		},

		errorClass: "has-error",
		errorLabelContainer: "#receipt_error_message_box",
		wrapper: "li",
		highlight: function (e)	{
			$(e).closest('.form-group').addClass('has-error');
		},
		unhighlight: function (e) {
			$(e).closest('.form-group').removeClass('has-error');
		},

		rules: 
		{
	
   		},

		messages: 
		{

		}
	});
});
</script>

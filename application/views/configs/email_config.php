<?php echo form_open('config/save_email/', array('id' => 'email_config_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal')); ?>
	<div id="config_wrapper">
		<fieldset id="config_email">
			<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
			<ul id="email_error_message_box" class="error_message_box"></ul>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_email_protocol'), 'protocol', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_dropdown('protocol', array(
						'mail' => 'mail',
						'sendmail' => 'sendmail',
						'smtp' => 'smtp'
					),
					$this->config->item('protocol'), array('class' => 'form-control input-sm', 'id' => 'protocol'));
					?>
				</div>
			</div>

			<div class="form-group form-group-sm">	
				<?php echo form_label($this->lang->line('config_email_mailpath'), 'mailpath', array('class' => 'control-label col-xs-2')); ?>
				<div class="col-xs-4">
					<?php echo form_input(array(
						'name' => 'mailpath',
						'id' => 'mailpath',
						'class' => 'form-control input-sm',
						'value' => $this->config->item('mailpath'))); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">	
				<?php echo form_label($this->lang->line('config_email_smtp_host'), 'smtp_host', array('class' => 'control-label col-xs-2')); ?>
				<div class="col-xs-2">
					<?php echo form_input(array(
						'name' => 'smtp_host',
						'id' => 'smtp_host',
						'class' => 'form-control input-sm',
						'value' => $this->config->item('smtp_host'))); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">	
				<?php echo form_label($this->lang->line('config_email_smtp_port'), 'smtp_port', array('class' => 'control-label col-xs-2')); ?>
				<div class="col-xs-2">
					<?php echo form_input(array(
						'name' => 'smtp_port',
						'id' => 'smtp_port',
						'class' => 'form-control input-sm',
						'value' => $this->config->item('smtp_port'))); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_email_smtp_crypto'), 'smtp_crypto', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_dropdown('smtp_crypto', array(
						'' => 'None',
						'tls' => 'TLS',
						'ssl' => 'SSL'
					),
					$this->config->item('smtp_crypto'), array('class' => 'form-control input-sm', 'id' => 'smtp_crypto'));
					?>
				</div>
			</div>

			<div class="form-group form-group-sm">	
				<?php echo form_label($this->lang->line('config_email_smtp_timeout'), 'smtp_timeout', array('class' => 'control-label col-xs-2')); ?>
				<div class="col-xs-2">
					<?php echo form_input(array(
						'name' => 'smtp_timeout',
						'id' => 'smtp_timeout',
						'class' => 'form-control input-sm',
						'value' => $this->config->item('smtp_timeout'))); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">	
				<?php echo form_label($this->lang->line('config_email_smtp_user'), 'smtp_user', array('class' => 'control-label col-xs-2')); ?>
				<div class="col-xs-4">
					<div class="input-group">
						<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-user"></span></span>
						<?php echo form_input(array(
							'name' => 'smtp_user',
							'id' => 'smtp_user',
							'class' => 'form-control input-sm',
							'value' => $this->config->item('smtp_user'))); ?>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">	
				<?php echo form_label($this->lang->line('config_email_smtp_pass'), 'smtp_pass', array('class' => 'control-label col-xs-2')); ?>
				<div class="col-xs-4">
					<div class="input-group">
						<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-asterisk"></span></span>
						<?php echo form_password(array(
							'name' => 'smtp_pass',
							'id' => 'smtp_pass',
							'class' => 'form-control input-sm',
							'value' => $this->config->item('smtp_pass'))); ?>
					</div>
				</div>
			</div>

			<?php echo form_submit(array(
				'name' => 'submit_form',
				'id' => 'submit_form',
				'value' => $this->lang->line('common_submit'),
				'class' => 'btn btn-primary btn-sm pull-right')); ?>
		</fieldset>
	</div>
<?php echo form_close(); ?>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{
	var check_protocol = function() {
		if($("#protocol").val() == 'sendmail')
		{
			$("#mailpath").prop('disabled', false);
			$("#smtp_host, #smtp_user, #smtp_pass, #smtp_port, #smtp_timeout, #smtp_crypto").prop('disabled', true);
		}
		else if($("#protocol").val() == 'smtp')
		{
			$("#smtp_host, #smtp_user, #smtp_pass, #smtp_port, #smtp_timeout, #smtp_crypto").prop('disabled', false);
			$("#mailpath").prop('disabled', true);
		}
		else
		{
			$("#mailpath, #smtp_host, #smtp_user, #smtp_pass, #smtp_port, #smtp_timeout, #smtp_crypto").prop('disabled', true);
		}
	};

	$("#protocol").change(check_protocol).ready(check_protocol);

	$('#email_config_form').validate($.extend(form_support.handler, {
		submitHandler: function(form) {
			$(form).ajaxSubmit({
				beforeSerialize: function(arr, $form, options) {
					$("#mailpath, #smtp_host, #smtp_user, #smtp_pass, #smtp_port, #smtp_timeout, #smtp_crypto").prop("disabled", false); 
					return true;
				},
				success: function(response) {
					$.notify(response.message, { type: response.success ? 'success' : 'danger'} );
					// set back disabled state
					check_protocol();
				},
				dataType:'json'
			});
		},
		
		errorLabelContainer: "#email_error_message_box"
	}));
});
</script>

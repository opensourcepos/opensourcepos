<?= form_open('config/save_email/', array('id' => 'email_config_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal')); ?>

<?php
$title_email['config_title'] = $this->lang->line('config_email_configuration');
$this->load->view('configs/config_header', $title_email);
?>

<ul id="email_error_message_box" class="error_message_box"></ul>

<div class="row">
	<div class="col-12 col-lg-6">
		<label for="protocol" class="form-label"><?= $this->lang->line('config_email_protocol'); ?></label>
		<div class="input-group mb-3">
			<label class="input-group-text"><i class="bi bi-mailbox"></i></label>
			<?= form_dropdown('protocol', array('mail' => 'mail', 'sendmail' => 'sendmail', 'smtp' => 'smtp'), $this->config->item('protocol'), array('class' => 'form-select', 'id' => 'protocol')); ?>
		</div>
	</div>

	<div class="col-12 col-lg-6">
		<label for="mailpath" class="form-label"><?= $this->lang->line('config_email_mailpath'); ?></label>
		<div class="input-group mb-3">
			<label class="input-group-text"><i class="bi bi-braces"></i></label>
			<input type="text" name="mailpath" class="form-control" id="mailpath" value="<?= $this->config->item('mailpath'); ?>">
		</div>
	</div>

	<div class="col-12 col-lg-6">
		<label for="smtp_host" class="form-label"><?= $this->lang->line('config_email_smtp_host'); ?></label>
		<div class="input-group mb-3">
			<label class="input-group-text"><i class="bi bi-server"></i></label>
			<input type="text" name="smtp_host" class="form-control" id="smtp_host" value="<?= $this->config->item('smtp_host'); ?>">
		</div>
	</div>

	<div class="col-12 col-lg-6">
		<label for="smtp_port" class="form-label"><?= $this->lang->line('config_email_smtp_port'); ?></label>
		<div class="input-group mb-3">
			<label class="input-group-text"><i class="bi bi-door-open"></i></label>
			<input type="number" name="smtp_port" class="form-control" id="smtp_port" value="<?= $this->config->item('smtp_port'); ?>">
		</div>
	</div>

	<div class="col-12 col-lg-6">
		<label for="smtp_crypto" class="form-label"><?= $this->lang->line('config_email_smtp_crypto'); ?></label>
		<div class="input-group mb-3">
			<label class="input-group-text"><i class="bi bi-shield-lock"></i></label>
			<?= form_dropdown('protocol', array('' => 'None', 'tls' => 'TLS', 'ssl' => 'SSL'), $this->config->item('smtp_crypto'), array('class' => 'form-select', 'id' => 'smtp_crypto')); ?>
		</div>
	</div>

	<div class="col-12 col-lg-6">
		<label for="smtp_timeout" class="form-label"><?= $this->lang->line('config_email_smtp_timeout'); ?></label>
		<div class="input-group mb-3">
			<label class="input-group-text"><i class="bi bi-stopwatch"></i></label>
			<input type="number" name="smtp_timeout" class="form-control" id="smtp_timeout" value="<?= $this->config->item('smtp_timeout'); ?>">
		</div>
	</div>

	<div class="col-12 col-lg-6">
		<label for="smtp_user" class="form-label"><?= $this->lang->line('config_email_smtp_user'); ?></label>
		<div class="input-group mb-3">
			<label class="input-group-text"><i class="bi bi-person"></i></label>
			<input type="text" name="smtp_user" class="form-control" id="smtp_user" value="<?= $this->config->item('smtp_user'); ?>">
		</div>
	</div>

	<div class="col-12 col-lg-6">
		<label for="smtp_pass" class="form-label"><?= $this->lang->line('config_email_smtp_pass'); ?></label>
		<div class="input-group mb-3">
			<label class="input-group-text"><i class="bi bi-lock"></i></label>
			<input type="password" name="smtp_pass" class="form-control" id="smtp_pass" value="<?= $this->config->item('smtp_pass'); ?>">
		</div>
	</div>
</div>

<div class="d-flex justify-content-end">
	<button class="btn btn-primary" name="submit_email"><?= $this->lang->line('common_submit'); ?></button>
</div>

<?= form_close(); ?>

<script type="text/javascript">
	//validation and submit handling
	$(document).ready(function() {
		var check_protocol = function() {
			if ($('#protocol').val() == 'sendmail') {
				$('#mailpath').prop('disabled', false);
				$('#smtp_host, #smtp_user, #smtp_pass, #smtp_port, #smtp_timeout, #smtp_crypto').prop('disabled', true);
			} else if ($('#protocol').val() == 'smtp') {
				$('#smtp_host, #smtp_user, #smtp_pass, #smtp_port, #smtp_timeout, #smtp_crypto').prop('disabled', false);
				$('#mailpath').prop('disabled', true);
			} else {
				$('#mailpath, #smtp_host, #smtp_user, #smtp_pass, #smtp_port, #smtp_timeout, #smtp_crypto').prop('disabled', true);
			}
		};

		$('#protocol').change(check_protocol).ready(check_protocol);

		$('#email_config_form').validate($.extend(form_support.handler, {
			submitHandler: function(form) {
				$(form).ajaxSubmit({
					beforeSerialize: function(arr, $form, options) {
						$('#mailpath, #smtp_host, #smtp_user, #smtp_pass, #smtp_port, #smtp_timeout, #smtp_crypto').prop('disabled', false);
						return true;
					},
					success: function(response) {
						$.notify({
							message: response.message
						}, {
							type: response.success ? 'success' : 'danger'
						})
						// set back disabled state
						check_protocol();
					},
					dataType: 'json'
				});
			},

			errorLabelContainer: '#email_error_message_box'
		}));
	});
</script>
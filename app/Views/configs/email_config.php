<?php
/**
 * @var array $config
 */
?>
<?= form_open('config/saveEmail/', ['id' => 'email_config_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal']) ?>
	<div id="config_wrapper">
		<fieldset id="config_info">
			<div id="required_fields_message"><?= lang('Common.fields_required_message') ?></div>
			<ul id="email_error_message_box" class="error_message_box"></ul>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.email_protocol'), 'protocol', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?= form_dropdown(
						'protocol',
						[
							'mail' => 'mail',
							'sendmail' => 'sendmail',
							'smtp' => 'smtp'
						],
						$config['protocol'],
						"class='form-control input-sm' id='protocol'"
					) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.email_mailpath'), 'mailpath', ['class' => 'control-label col-xs-2']) ?>
				<div class="col-xs-4">
					<?= form_input ([
						'name' => 'mailpath',
						'id' => 'mailpath',
						'class' => 'form-control input-sm',
						'value' => $config['mailpath']
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.email_smtp_host'), 'smtp_host', ['class' => 'control-label col-xs-2']) ?>
				<div class="col-xs-2">
					<?= form_input ([
						'name' => 'smtp_host',
						'id' => 'smtp_host',
						'class' => 'form-control input-sm',
						'value' => $config['smtp_host']
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.email_smtp_port'), 'smtp_port', ['class' => 'control-label col-xs-2']) ?>
				<div class="col-xs-2">
					<?= form_input ([
						'name' => 'smtp_port',
						'id' => 'smtp_port',
						'class' => 'form-control input-sm',
						'value' => $config['smtp_port']
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.email_smtp_crypto'), 'smtp_crypto', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?= form_dropdown(
						'smtp_crypto',
						[
							'' => 'None',
							'tls' => 'TLS',
							'ssl' => 'SSL'
						],
						$config['smtp_crypto'],
						"class='form-control input-sm' id='smtp_crypto'"
					) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.email_smtp_timeout'), 'smtp_timeout', ['class' => 'control-label col-xs-2']) ?>
				<div class="col-xs-2">
					<?= form_input ([
						'name' => 'smtp_timeout',
						'id' => 'smtp_timeout',
						'class' => 'form-control input-sm',
						'value' => $config['smtp_timeout']
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.email_smtp_user'), 'smtp_user', ['class' => 'control-label col-xs-2']) ?>
				<div class="col-xs-4">
					<div class="input-group">
						<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-user"></span></span>
						<?= form_input ([
							'name' => 'smtp_user',
							'id' => 'smtp_user',
							'class' => 'form-control input-sm',
							'value' => $config['smtp_user']
						]) ?>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.email_smtp_pass'), 'smtp_pass', ['class' => 'control-label col-xs-2']) ?>
				<div class="col-xs-4">
					<div class="input-group">
						<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-asterisk"></span></span>
						<?= form_password ([
							'name' => 'smtp_pass',
							'id' => 'smtp_pass',
							'class' => 'form-control input-sm',
							'value' => $config['smtp_pass']
						]) ?>
					</div>
				</div>
			</div>

			<?= form_submit ([
				'name' => 'submit_email',
				'id' => 'submit_email',
				'value' => lang('Common.submit'),
				'class' => 'btn btn-primary btn-sm pull-right']) ?>
		</fieldset>
	</div>
<?= form_close() ?>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{
	var check_protocol = function() {
		if($('#protocol').val() == 'sendmail')
		{
			$('#mailpath').prop('disabled', false);
			$('#smtp_host, #smtp_user, #smtp_pass, #smtp_port, #smtp_timeout, #smtp_crypto').prop('disabled', true);
		}
		else if($('#protocol').val() == 'smtp')
		{
			$('#smtp_host, #smtp_user, #smtp_pass, #smtp_port, #smtp_timeout, #smtp_crypto').prop('disabled', false);
			$('#mailpath').prop('disabled', true);
		}
		else
		{
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
					$.notify( { message: response.message }, { type: response.success ? 'success' : 'danger'} )
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

<?= form_open('config/save_mailchimp/', array('id' => 'mailchimp_config_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal')); ?>

<?php
$title_integrations['config_title'] = $this->lang->line('config_integrations_configuration');
$this->load->view('configs/config_header', $title_integrations);
?>

<h5 class="mb-3">Google reCAPTCHA</h5>

<div class="form-check form-switch mb-3">
	<input class="form-check-input" type="checkbox" id="gcaptcha-enable" checked="<?= $this->config->item('gcaptcha_enable'); ?>">
	<label class="form-check-label" for="gcaptcha-enable"><?= $this->lang->line('config_gcaptcha_enable'); ?></label>
	<i class="bi bi-info-circle-fill text-secondary" role="button" tabindex="0" data-bs-toggle="tooltip" title="<?= $this->lang->line('config_gcaptcha_tooltip'); ?>"></i>
	<a class="d-inline-block" href="https://google.com/recaptcha/admin" target="_blank" rel="noopener">
		<i class="bi bi-link-45deg link-secondary"></i>
	</a>
</div>

<div class="row">
	<div class="col-12 col-lg-6">
		<label for="gcaptcha-site-key" class="form-label"><?= $this->lang->line('config_gcaptcha_site_key'); ?></label>
		<div class="input-group mb-3">
			<span class="input-group-text"><i class="bi bi-key"></i></span>
			<input type="text" class="form-control" id="gcaptcha-site-key" required>
		</div>
	</div>
	<div class="col-12 col-lg-6">
		<label for="gcaptcha-secret-key" class="form-label"><?= $this->lang->line('config_gcaptcha_secret_key'); ?></label>
		<div class="input-group">
			<span class="input-group-text"><i class="bi bi-stars"></i></span>
			<input type="text" class="form-control" id="gcaptcha-secret-key" required>
		</div>
	</div>
</div>

<hr class="my-4">

<h5><?= $this->lang->line('config_mailchimp_configuration') ?></h5>
<ul id="mailchimp_error_message_box" class="error_message_box"></ul>

<div class="row">
	<div class="col-12 col-lg-6">
		<label for="mailchimp-api-key" class="form-label">
			<?= $this->lang->line('config_mailchimp_api_key'); ?>&nbsp;
			<a class="d-none d-lg-inline-block" href="http://eepurl.com/b9a05b" target="_blank" rel="noopener">
				<i class="bi bi-info-circle-fill text-secondary" data-bs-toggle="tooltip" title="<?= $this->lang->line('config_mailchimp_tooltip'); ?>"></i>
			</a>
		</label>
		<div class="input-group mb-3">
			<span class="input-group-text" id="mailchimp-api-key-icon"><i class="bi bi-key"></i></span>
			<input type="text" class="form-control" id="mailchimp-api-key" aria-describedby="mailchimp-api-key-icon">
		</div>
	</div>
	<div class="col-12 col-lg-6">
		<label for="mailchimp-lists" class="form-label"><?= $this->lang->line('config_mailchimp_lists'); ?></label>
		<div class="input-group mb-3">
			<span class="input-group-text" id="mailchimp-lists-icon"><i class="bi bi-person-lines-fill"></i></span>
			<select class="form-select" id="mailchimp-lists" aria-describedby="mailchimp-lists-icon" disabled>
				<option selected>Choose...</option>
				<option value="1">One</option>
				<option value="2">Two</option>
				<option value="3">Three</option>
			</select>
		</div>
	</div>
</div>

<div class="d-flex justify-content-end">
	<button class="btn btn-primary" name="sumbit_mailchimp"><?= $this->lang->line('common_submit'); ?></button>
</div>

<?php echo form_close(); ?>

<script type="text/javascript">
	//validation and submit handling
	document.querySelector(document).ready(function() {
		document.querySelector('#mailchimp_api_key').change(function() {
			$.post("<?php echo site_url($controller_name . '/ajax_check_mailchimp_api_key') ?>", {
					'mailchimp_api_key': document.querySelector('#mailchimp_api_key').value
				},
				function(response) {
					$.notify({
						message: response.message
					}, {
						type: response.success ? 'success' : 'danger'
					});
					document.querySelector('#mailchimp_list_id').empty();
					$.each(response.mailchimp_lists, function(val, text) {
						document.querySelector('#mailchimp_list_id').insertAdjacentHTML("beforeend", new Option(text, val));
					});
					document.querySelector('#mailchimp_list_id').prop('selectedIndex', 0);
				},
				'json'
			);
		});

		document.querySelector('#mailchimp_config_form').validate($.extend(form_support.handler, {
			submitHandler: function(form) {
				document.querySelector(form).ajaxSubmit({
					success: function(response) {
						$.notify({
							message: response.message
						}, {
							type: response.success ? 'success' : 'danger'
						})
					},
					dataType: 'json'
				});
			},

			errorLabelContainer: '#mailchimp_error_message_box'
		}));
	});
	/*//validation and submit handling
	$(document).ready(function() {
		$('#mailchimp_api_key').change(function() {
			$.post("<?php echo site_url($controller_name . '/ajax_check_mailchimp_api_key') ?>", {
					'mailchimp_api_key': $('#mailchimp_api_key').val()
				},
				function(response) {
					$.notify({
						message: response.message
					}, {
						type: response.success ? 'success' : 'danger'
					});
					$('#mailchimp_list_id').empty();
					$.each(response.mailchimp_lists, function(val, text) {
						$('#mailchimp_list_id').append(new Option(text, val));
					});
					$('#mailchimp_list_id').prop('selectedIndex', 0);
				},
				'json'
			);
		});

		$('#mailchimp_config_form').validate($.extend(form_support.handler, {
			submitHandler: function(form) {
				$(form).ajaxSubmit({
					success: function(response) {
						$.notify({
							message: response.message
						}, {
							type: response.success ? 'success' : 'danger'
						})
					},
					dataType: 'json'
				});
			},

			errorLabelContainer: '#mailchimp_error_message_box'
		}));
	});*/
</script>
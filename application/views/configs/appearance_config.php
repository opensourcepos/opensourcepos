<?= form_open('config/save_appearance/', array('id' => 'appearance_config_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal')); ?>

<?php
$title_appearance['config_title'] = 'Appearance Configuration';
$this->load->view('configs/config_header', $title_appearance);
?>

<ul id="appearance_error_message_box" class="error_message_box"></ul>

<div class="row">
	<div class="col-12 col-lg-6">
		<label for="theme-change" class="form-label"><?= $this->lang->line('config_theme'); ?></label>
		<div class="input-group mb-3">
			<label class="input-group-text"><i class="bi bi-binoculars"></i></label>
			<?= form_dropdown('theme', $themes, $this->config->item('theme'), array('class' => 'form-select', 'id' => 'theme-change')); ?>
		</div>
	</div>

	<div class="col-12 col-lg-6">
		<label for="login_form" class="form-label"><?= $this->lang->line('config_login_form'); ?></label>
		<div class="input-group mb-3">
			<label class="input-group-text"><i class="bi bi-view-stacked"></i></label>
			<?= form_dropdown('login_form', array('floating_labels' => $this->lang->line('config_floating_labels'), 'input_groups' => $this->lang->line('config_input_groups')), $this->config->item('login_form'), array('class' => 'form-select')); ?>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-12 col-lg-6">
		<label for="notify-position" class="form-label"><?= $this->lang->line('config_notify_alignment'); ?></label>
		<div class="row" id="notify-position">
			<div class="col-6 mb-3">
				<div class="input-group">
					<label class="input-group-text"><i class="bi bi-arrow-down-up"></i></label>
					<?= form_dropdown('notify_vertical_position', array('top' => $this->lang->line('config_top'), 'bottom' => $this->lang->line('config_bottom')), $this->config->item('notify_vertical_position'), array('class' => 'form-select')); ?>
				</div>
			</div>
			<div class="col-6 mb-3">
				<div class="input-group">
					<label class="input-group-text"><i class="bi bi-arrow-left-right"></i></label>
					<?= form_dropdown('notify_horizontal_position', array('left' => $this->lang->line('config_left'), 'center' => $this->lang->line('config_center'), 'right' => $this->lang->line('config_right')), $this->config->item('notify_horizontal_position'), array('class' => 'form-select')); ?>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="d-flex justify-content-end">
	<button class="btn btn-primary" name="submit_appearance"><?= $this->lang->line('common_submit'); ?></button>
</div>

<?= form_close(); ?>

<script type="text/javascript">
	//validation and submit handling
	$(document).ready(function() {
		var enable_disable_gcaptcha_enable = (function() {
			var gcaptcha_enable = $("#gcaptcha_enable").is(":checked");
			if (gcaptcha_enable) {
				$("#gcaptcha_site_key, #gcaptcha_secret_key").prop("disabled", !gcaptcha_enable).addClass("required");
				$("#config_gcaptcha_site_key, #config_gcaptcha_secret_key").addClass("required");
			} else {
				$("#gcaptcha_site_key, #gcaptcha_secret_key").prop("disabled", gcaptcha_enable).removeClass("required");
				$("#config_gcaptcha_site_key, #config_gcaptcha_secret_key").removeClass("required");
			}

			return arguments.callee;
		})();

		$("#gcaptcha_enable").change(enable_disable_gcaptcha_enable);

		$("#backup_db").click(function() {
			window.location = '<?= site_url('config/backup_db') ?>';
		});

		$('#general_config_form').validate($.extend(form_support.handler, {

			errorLabelContainer: "#general_error_message_box",

			rules: {
				lines_per_page: {
					required: true,
					remote: "<?= site_url($controller_name . '/check_numeric') ?>"
				},
				default_sales_discount: {
					required: true,
					remote: "<?= site_url($controller_name . '/check_numeric') ?>"
				},
				gcaptcha_site_key: {
					required: "#gcaptcha_enable:checked"
				},
				gcaptcha_secret_key: {
					required: "#gcaptcha_enable:checked"
				}
			},

			messages: {
				default_sales_discount: {
					required: "<?= $this->lang->line('config_default_sales_discount_required'); ?>",
					number: "<?= $this->lang->line('config_default_sales_discount_number'); ?>"
				},
				lines_per_page: {
					required: "<?= $this->lang->line('config_lines_per_page_required'); ?>",
					number: "<?= $this->lang->line('config_lines_per_page_number'); ?>"
				},
				gcaptcha_site_key: {
					required: "<?= $this->lang->line('config_gcaptcha_site_key_required'); ?>"
				},
				gcaptcha_secret_key: {
					required: "<?= $this->lang->line('config_gcaptcha_secret_key_required'); ?>"
				}
			},

			submitHandler: function(form) {
				$(form).ajaxSubmit({
					beforeSerialize: function(arr, $form, options) {
						$("#gcaptcha_site_key, #gcaptcha_secret_key").prop("disabled", false);
						return true;
					},
					success: function(response) {
						$.notify({
							message: response.message
						}, {
							type: response.success ? 'success' : 'danger'
						})
						// set back disabled state
						enable_disable_gcaptcha_enable();
					},
					dataType: 'json'
				});
			}
		}));
	});
</script>
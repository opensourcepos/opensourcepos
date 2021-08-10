<?php echo form_open('config/save_general/', array('id' => 'general_config_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal')); ?>

<?php
$title_general['config_title'] = $this->lang->line('config_general_configuration');
$this->load->view('configs/config_header', $title_general);
?>

<ul id="general_error_message_box" class="error_message_box"></ul>

<div class="row">
	<div class="col-12 col-sm-6 col-xxl-3">
		<label for="default-sales-discount" class="form-label"><?= $this->lang->line('config_default_sales_discount'); ?></label><span class="text-warning">*</span>
		<div class="input-group mb-3">
			<span class="input-group-text"><i class="bi bi-cart-dash"></i></span>
			<input type="number" min="0" max="100" name="default_sales_discount" class="form-control" id="default-sales-discount" value="<?= $this->config->item('default_sales_discount'); ?>">
			<span class="input-group-text"><i class="bi bi-percent"></i></span>
		</div>
	</div>

	<div class="col-12 col-sm-6 col-xxl-3">
		<label for="default-receivings-discount" class="form-label"><?= $this->lang->line('config_default_receivings_discount'); ?></label><span class="text-warning">*</span>
		<div class="input-group mb-3">
			<span class="input-group-text"><i class="bi bi-bag-dash"></i></span>
			<input type="number" min="0" max="100" name="default_receivings_discount" class="form-control" id="default-receivings-discount" value="<?= $this->config->item('default_receivings_discount'); ?>" required>
			<span class="input-group-text"><i class="bi bi-percent"></i></span>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-12 col-sm-6 col-xxl-3">
		<label for="lines-per-page" class="form-label"><?= $this->lang->line('config_lines_per_page'); ?></label><span class="text-warning">*</span>
		<div class="input-group mb-3">
			<span class="input-group-text"><i class="bi bi-list"></i></span>
			<input type="number" min="10" max="1000" name="lines_per_page" class="form-control" id="lines-per-page" value="<?= $this->config->item('lines_per_page'); ?>" required>
		</div>
	</div>
</div>

<div class="form-check form-switch mb-3">
	<input class="form-check-input" type="checkbox" id="enforce-privacy" name="enforce_privacy" checked="<?= $this->config->item('enforce_privacy'); ?>">
	<label class="form-check-label" for="enforce-privacy"><?= $this->lang->line('config_enforce_privacy'); ?></label>
	<i class="bi bi-info-circle-fill text-secondary" role="button" tabindex="0" data-bs-toggle="tooltip" title="<?= $this->lang->line('config_enforce_privacy_tooltip'); ?>"></i>
</div>

<div class="form-check form-switch mb-3">
	<input class="form-check-input" type="checkbox" id="receiving-calculate-average-price" name="receiving_calculate_average_price" checked="<?= $this->config->item('receiving_calculate_average_price'); ?>">
	<label class="form-check-label" for="receiving-calculate-average-price"><?= $this->lang->line('config_receiving_calculate_average_price'); ?></label>
</div>

<div class="row">
	<label class="form-label"><?= $this->lang->line('config_image_restrictions'); ?></label>
	<div class="col-12 col-sm-6 col-xl-3">
		<div class="input-group mb-3">
			<span class="input-group-text"><i class="bi bi-arrow-left-right"></i></span>
			<input type="number" min="128" max="3840" name="image_max_width" class="form-control" value="<?= $this->config->item('image_max_width'); ?>" required>
			<span class="input-group-text">px</span>
		</div>
	</div>

	<div class="col-12 col-sm-6 col-xl-3">
		<div class="input-group mb-3">
			<span class="input-group-text"><i class="bi bi-arrow-down-up"></i></span>
			<input type="number" min="128" max="3840" name="image_max_height" class="form-control" value="<?= $this->config->item('image_max_height'); ?>" required>
			<span class="input-group-text">px</span>
		</div>
	</div>

	<div class="col-12 col-sm-6 col-xl-3">
		<div class="input-group mb-3">
			<span class="input-group-text"><i class="bi bi-hdd"></i></span>
			<input type="number" min="128" max="2048" name="image_max_size" class="form-control" value="<?= $this->config->item('image_max_size'); ?>" required>
			<span class="input-group-text">kb</span>
		</div>
	</div>

	<div class="col-12 col-sm-6 col-xl-3">
		<div class="input-group mb-3">
			<label class="input-group-text"><i class="bi bi-images"></i></label>
			<?= form_dropdown('image_allowed_types[]', $image_allowed_types, $selected_image_allowed_types, array('class' => 'form-select', 'id' => 'image-allowed-types', 'multiple' => '')); ?>
		</div>
	</div>
</div>

<div class="form-group form-group-sm">
	<?php echo form_label($this->lang->line('config_image_restrictions'), 'image_restrictions', array('class' => 'control-label col-xs-2')); ?>
	<div class="col-sm-10">
		<div class="form-group form-group-sm row">
			<div class='col-sm-2'>
				<div class='input-group'>
					<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-resize-horizontal"></span></span>
					<?php echo form_input(array(
						'name' => 'image_max_width',
						'id' => 'image_max_width',
						'class' => 'form-control input-sm required',
						'type' => 'number',
						'min' => 128,
						'max' => 3840,
						'value' => $this->config->item('image_max_width'),
						'data-toggle' => 'tooltip',
						'data-placement' => 'top',
						'title' => $this->lang->line('config_image_max_width_tooltip')
					));
					?>
				</div>
			</div>
			<div class='col-sm-2'>
				<div class='input-group'>
					<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-resize-vertical"></span></span>
					<?php echo form_input(array(
						'name' => 'image_max_height',
						'id' => 'image_max_height',
						'class' => 'form-control input-sm required',
						'type' => 'number',
						'min' => 128,
						'max' => 3840,
						'value' => $this->config->item('image_max_height'),
						'data-toggle' => 'tooltip',
						'data-placement' => 'top',
						'title' => $this->lang->line('config_image_max_height_tooltip')
					));
					?>
				</div>
			</div>
			<div class='col-sm-2'>
				<div class='input-group'>
					<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-hdd"></span></span>
					<?php echo form_input(array(
						'name' => 'image_max_size',
						'id' => 'image_max_size',
						'class' => 'form-control input-sm required',
						'type' => 'number',
						'min' => 128,
						'max' => 2048,
						'value' => $this->config->item('image_max_size'),
						'data-toggle' => 'tooltip',
						'data-placement' => 'top',
						'title' => $this->lang->line('config_image_max_size_tooltip')
					));
					?>
				</div>
			</div>
			<div class='col-sm-4'>
				<div class='input-group'>
					<span class="input-group-addon input-sm"><?php echo $this->lang->line('config_image_allowed_file_types'); ?></span>
					<?php echo form_multiselect('image_allowed_types[]', $image_allowed_types, $selected_image_allowed_types, array(
						'id' => 'image_allowed_types',
						'class' => 'selectpicker show-menu-arrow',
						'data-none-selected-text' => $this->lang->line('common_none_selected_text'),
						'data-selected-text-format' => 'count > 1',
						'data-style' => 'btn-default btn-sm',
						'data-width' => '100%'
					));
					?>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="form-group form-group-sm">
	<?php echo form_label($this->lang->line('config_suggestions_layout'), 'suggestions_layout', array('class' => 'control-label col-xs-2')); ?>
	<div class="col-sm-10">
		<div class="form-group form-group-sm row">
			<div class='col-sm-3'>
				<div class='input-group'>
					<span class="input-group-addon input-sm"><?php echo $this->lang->line('config_suggestions_first_column'); ?></span>
					<?php echo form_dropdown(
						'suggestions_first_column',
						array(
							'name' => $this->lang->line('items_name'),
							'item_number' => $this->lang->line('items_number_information'),
							'unit_price' => $this->lang->line('items_unit_price'),
							'cost_price' => $this->lang->line('items_cost_price')
						),
						$this->config->item('suggestions_first_column'),
						array('class' => 'form-control input-sm')
					); ?>
				</div>
			</div>
			<div class='col-sm-3'>
				<div class='input-group'>
					<span class="input-group-addon input-sm"><?php echo $this->lang->line('config_suggestions_second_column'); ?></span>
					<?php echo form_dropdown(
						'suggestions_second_column',
						array(
							'' => $this->lang->line('config_none'),
							'name' => $this->lang->line('items_name'),
							'item_number' => $this->lang->line('items_number_information'),
							'unit_price' => $this->lang->line('items_unit_price'),
							'cost_price' => $this->lang->line('items_cost_price')
						),
						$this->config->item('suggestions_second_column'),
						array('class' => 'form-control input-sm')
					); ?>
				</div>
			</div>
			<div class='col-sm-3'>
				<div class='input-group'>
					<span class="input-group-addon input-sm"><?php echo $this->lang->line('config_suggestions_third_column'); ?></span>
					<?php echo form_dropdown(
						'suggestions_third_column',
						array(
							'' => $this->lang->line('config_none'),
							'name' => $this->lang->line('items_name'),
							'item_number' => $this->lang->line('items_number_information'),
							'unit_price' => $this->lang->line('items_unit_price'),
							'cost_price' => $this->lang->line('items_cost_price')
						),
						$this->config->item('suggestions_third_column'),
						array('class' => 'form-control input-sm')
					); ?>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="form-group form-group-sm">
	<?php echo form_label($this->lang->line('config_giftcard_number'), 'giftcard_number', array('class' => 'control-label col-xs-2')); ?>
	<div class='col-xs-8'>
		<label class="radio-inline">
			<?php echo form_radio(array(
				'name' => 'giftcard_number',
				'value' => 'series',
				'checked' => $this->config->item('giftcard_number') == 'series'
			)); ?>
			<?php echo $this->lang->line('config_giftcard_series'); ?>
		</label>
		<label class="radio-inline">
			<?php echo form_radio(array(
				'name' => 'giftcard_number',
				'value' => 'random',
				'checked' => $this->config->item('giftcard_number') == 'random'
			)); ?>
			<?php echo $this->lang->line('config_giftcard_random'); ?>
		</label>
	</div>
</div>

<div class="form-group form-group-sm">
	<?php echo form_label($this->lang->line('config_derive_sale_quantity'), 'derive_sale_quantity', array('class' => 'control-label col-xs-2')); ?>
	<div class='col-xs-1'>
		<?php echo form_checkbox(array(
			'name' => 'derive_sale_quantity',
			'id' => 'derive_sale_quantity',
			'value' => 'derive_sale_quantity',
			'checked' => $this->config->item('derive_sale_quantity')
		)); ?>
		&nbsp
		<label class="control-label">
			<span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-placement="right" title="<?php echo $this->lang->line('config_derive_sale_quantity_tooltip'); ?>"></span>
		</label>
	</div>
</div>

<div class="form-group form-group-sm">
	<?php echo form_label($this->lang->line('config_show_office_group'), 'show_office_group', array('class' => 'control-label col-xs-2')); ?>
	<div class='col-xs-1'>
		<?php echo form_checkbox(array(
			'name' => 'show_office_group',
			'id' => 'show_office_group',
			'value' => 'show_office_group',
			'checked' => $show_office_group
		)); ?>
	</div>
</div>

<div class="form-group form-group-sm">
	<?php echo form_label($this->lang->line('config_multi_pack_enabled'), 'multi_pack_enabled', array('class' => 'control-label col-xs-2')); ?>
	<div class='col-xs-1'>
		<?php echo form_checkbox(array(
			'name' => 'multi_pack_enabled',
			'id' => 'multi_pack_enabled',
			'value' => 'multi_pack_enabled',
			'checked' => $this->config->item('multi_pack_enabled')
		)); ?>
	</div>
</div>

<div class="form-group form-group-sm">
	<?php echo form_label($this->lang->line('config_include_hsn'), 'include_hsn', array('class' => 'control-label col-xs-2')); ?>
	<div class='col-xs-1'>
		<?php echo form_checkbox(array(
			'name' => 'include_hsn',
			'id' => 'include_hsn',
			'value' => 'include_hsn',
			'checked' => $this->config->item('include_hsn')
		)); ?>
	</div>
</div>

<div class="form-group form-group-sm">
	<?php echo form_label($this->lang->line('config_category_dropdown'), 'category_dropdown', array('class' => 'control-label col-xs-2')); ?>
	<div class='col-xs-1'>
		<?php echo form_checkbox(array(
			'name' => 'category_dropdown',
			'id' => 'category_dropdown',
			'value' => 'category_dropdown',
			'checked' => $this->config->item('category_dropdown')
		)); ?>
	</div>
</div>

<div class="form-group form-group-sm">
	<?php echo form_label($this->lang->line('config_backup_database'), 'config_backup_database', array('class' => 'control-label col-xs-2')); ?>
	<div class='col-xs-2'>
		<div id="backup_db" class="btn btn-default btn-sm">
			<span style="top:22%;"><?php echo $this->lang->line('config_backup_button'); ?></span>
		</div>
	</div>
</div>

<div class="d-flex justify-content-end">
	<button class="btn btn-primary" name="submit_general"><?= $this->lang->line('common_submit'); ?></button>
</div>

<div class="form-group form-group-sm">
	<?php echo form_label($this->lang->line('config_theme'), 'theme', array('class' => 'control-label col-xs-2')); ?>
	<div class='col-sm-10'>
		<div class="form-group form-group-sm row">
			<div class='col-sm-3'>
				<?php echo form_dropdown('theme', $themes, $this->config->item('theme'), array('class' => 'form-control input-sm', 'id' => 'theme-change')); ?>
			</div>
			<div class="col-sm-7">
				<a href="<?php echo 'https://bootswatch.com/3/' . ('bootstrap' == ($this->config->item('theme')) ? 'default' : $this->config->item('theme')); ?>" target="_blank" rel=”noopener”>
					<span><?php echo $this->lang->line('config_theme_preview') . ' ' . ucfirst($this->config->item('theme')) . ' '; ?></span><span class="glyphicon glyphicon-new-window"></span>
				</a>
			</div>
		</div>
	</div>
</div>

<div class="form-group form-group-sm">
	<?php echo form_label($this->lang->line('config_login_form'), 'login_form', array('class' => 'control-label col-xs-2')); ?>
	<div class='col-xs-2'>
		<?php echo form_dropdown(
			'login_form',
			array(
				'floating_labels' => $this->lang->line('config_floating_labels'),
				'input_groups' => $this->lang->line('config_input_groups')
			),
			$this->config->item('login_form'),
			array('class' => 'form-control input-sm')
		); ?>
	</div>
</div>

<div class="form-group form-group-sm">
	<?php echo form_label($this->lang->line('config_notify_alignment'), 'notify_horizontal_position', array('class' => 'control-label col-xs-2')); ?>
	<div class="col-sm-10">
		<div class="form-group form-group-sm row">
			<div class='col-sm-2'>
				<?php echo form_dropdown(
					'notify_vertical_position',
					array(
						'top' => $this->lang->line('config_top'),
						'bottom' => $this->lang->line('config_bottom')
					),
					$this->config->item('notify_vertical_position'),
					array('class' => 'form-control input-sm')
				); ?>
			</div>
			<div class='col-sm-2'>
				<?php echo form_dropdown(
					'notify_horizontal_position',
					array(
						'left' => $this->lang->line('config_left'),
						'center' => $this->lang->line('config_center'),
						'right' => $this->lang->line('config_right')
					),
					$this->config->item('notify_horizontal_position'),
					array('class' => 'form-control input-sm')
				); ?>
			</div>
		</div>
	</div>
</div>

<?php echo form_close(); ?>

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
			window.location = '<?php echo site_url('config/backup_db') ?>';
		});

		$('#general_config_form').validate($.extend(form_support.handler, {

			errorLabelContainer: "#general_error_message_box",

			rules: {
				lines_per_page: {
					required: true,
					remote: "<?php echo site_url($controller_name . '/check_numeric') ?>"
				},
				default_sales_discount: {
					required: true,
					remote: "<?php echo site_url($controller_name . '/check_numeric') ?>"
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
					required: "<?php echo $this->lang->line('config_default_sales_discount_required'); ?>",
					number: "<?php echo $this->lang->line('config_default_sales_discount_number'); ?>"
				},
				lines_per_page: {
					required: "<?php echo $this->lang->line('config_lines_per_page_required'); ?>",
					number: "<?php echo $this->lang->line('config_lines_per_page_number'); ?>"
				},
				gcaptcha_site_key: {
					required: "<?php echo $this->lang->line('config_gcaptcha_site_key_required'); ?>"
				},
				gcaptcha_secret_key: {
					required: "<?php echo $this->lang->line('config_gcaptcha_secret_key_required'); ?>"
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
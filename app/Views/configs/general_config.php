<?php echo form_open('config/save_general/', ['id' => 'general_config_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal')); ?>
	<div id="config_wrapper">
		<fieldset id="config_info">
			<div id="required_fields_message"><?php echo lang('Common.fields_required_message'); ?></div>
			<ul id="general_error_message_box" class="error_message_box"></ul>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.theme'), 'theme', ['class' => 'control-label col-xs-2')); ?>
				<div class='col-sm-10'>
					<div class="form-group form-group-sm row">
						<div class='col-sm-3'>
							<?php echo form_dropdown('theme', $themes, $this->config->get('theme'), ['class' => 'form-control input-sm', 'id' => 'theme-change')); ?>
						</div>
						<div class="col-sm-7">
							<a href="<?php echo 'https://bootswatch.com/3/' . ('bootstrap'==($this->config->get('theme')) ? 'default' : $this->config->get('theme')); ?>" target="_blank" rel=”noopener”>
								<span><?php echo lang('Config.theme_preview') . ' ' . ucfirst($this->config->get('theme')) . ' '; ?></span><span class="glyphicon glyphicon-new-window"></span>
							</a>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.login_form'), 'login_form', ['class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_dropdown('login_form', [
							'floating_labels' => lang('Config.floating_labels'),
							'input_groups' => lang('Config.input_groups')
						),
						$this->config->get('login_form'), ['class' => 'form-control input-sm')); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.default_sales_discount'), 'default_sales_discount', ['class' => 'control-label col-xs-2 required')); ?>
				<div class='col-xs-2'>
					<div class="input-group">
						<?php echo form_input ([
							'name' => 'default_sales_discount',
							'id' => 'default_sales_discount',
							'class' => 'form-control input-sm required',
							'type' => 'number',
							'min' => 0,
							'max' => 100,
							'value' => $this->config->get('default_sales_discount'))); ?>
						<span class="input-group-btn">
							<?php echo form_checkbox ([
								'id' => 'default_sales_discount_type',
								'name' => 'default_sales_discount_type',
								'value' => 1,
								'data-toggle' => 'toggle',
								'data-size' => 'normal',
								'data-onstyle' => 'success',
								'data-on' => '<b>'.$this->config->get('currency_symbol').'</b>',
								'data-off' => '<b>%</b>',
								'checked' => $this->config->get('default_sales_discount_type'))); ?>
						</span>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.default_receivings_discount'), 'default_receivings_discount', ['class' => 'control-label col-xs-2 required')); ?>
				<div class='col-xs-2'>
					<div class="input-group">
						<?php echo form_input ([
							'name' => 'default_receivings_discount',
							'id' => 'default_receivings_discount',
							'class' => 'form-control input-sm required',
							'type' => 'number',
							'min' => 0,
							'max' => 100,
							'value' => $this->config->get('default_receivings_discount'))); ?>
						<span class="input-group-btn">
							<?php echo form_checkbox ([
								'id' => 'default_receivings_discount_type',
								'name' => 'default_receivings_discount_type',
								'value' => 1,
								'data-toggle' => 'toggle',
								'data-size' => 'normal',
								'data-onstyle' => 'success',
								'data-on' => '<b>'.$this->config->get('currency_symbol').'</b>',
								'data-off' => '<b>%</b>',
								'checked' => $this->config->get('default_receivings_discount_type'))); ?>
						</span>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.enforce_privacy'), 'enforce_privacy', ['class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-1'>
					<?php echo form_checkbox ([
						'name' => 'enforce_privacy',
						'id' => 'enforce_privacy',
						'value' => 'enforce_privacy',
						'checked' => $this->config->get('enforce_privacy'))); ?>
					&nbsp
					<label class="control-label">
						<span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-placement="right" title="<?php echo lang('Config.enforce_privacy_tooltip'); ?>"></span>
					</label>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.receiving_calculate_average_price'), 'receiving_calculate_average_price', ['class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-1'>
					<?php echo form_checkbox ([
						'name' => 'receiving_calculate_average_price',
						'id' => 'receiving_calculate_average_price',
						'value' => 'receiving_calculate_average_price',
						'checked' => $this->config->get('receiving_calculate_average_price'))); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.lines_per_page'), 'lines_per_page', ['class' => 'control-label col-xs-2 required')); ?>
				<div class='col-xs-2'>
					<?php echo form_input ([
						'name' => 'lines_per_page',
						'id' => 'lines_per_page',
						'class' => 'form-control input-sm required',
						'type' => 'number',
						'min' => 10,
						'max' => 1000,
						'value' => $this->config->get('lines_per_page'))); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.notify_alignment'), 'notify_horizontal_position', ['class' => 'control-label col-xs-2')); ?>
				<div class="col-sm-10">
					<div class="form-group form-group-sm row">
						<div class='col-sm-2'>
							<?php echo form_dropdown('notify_vertical_position', [
									'top' => lang('Config.top'),
									'bottom' => lang('Config.bottom')
								),
								$this->config->get('notify_vertical_position'), ['class' => 'form-control input-sm')); ?>
						</div>
						<div class='col-sm-2'>
							<?php echo form_dropdown('notify_horizontal_position', [
									'left' => lang('Config.left'),
									'center' => lang('Config.center'),
									'right' => lang('Config.right')),
									$this->config->get('notify_horizontal_position'), ['class' => 'form-control input-sm')); ?>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.image_restrictions'), 'image_restrictions', ['class' => 'control-label col-xs-2')); ?>
				<div class="col-sm-10">
					<div class="form-group form-group-sm row">
						<div class='col-sm-2'>
							<div class='input-group'>
								<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-resize-horizontal"></span></span>
								<?php echo form_input ([
									'name' => 'image_max_width',
									'id' => 'image_max_width',
									'class' => 'form-control input-sm required',
									'type' => 'number',
									'min' => 128,
									'max' => 3840,
									'value' => $this->config->get('image_max_width'),
									'data-toggle' => 'tooltip',
									'data-placement' => 'top',
									'title' => lang('Config.image_max_width_tooltip')));
								?>
							</div>
						</div>
						<div class='col-sm-2'>
							<div class='input-group'>
								<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-resize-vertical"></span></span>
								<?php echo form_input ([
									'name' => 'image_max_height',
									'id' => 'image_max_height',
									'class' => 'form-control input-sm required',
									'type' => 'number',
									'min' => 128,
									'max' => 3840,
									'value' => $this->config->get('image_max_height'),
									'data-toggle' => 'tooltip',
									'data-placement' => 'top',
									'title' => lang('Config.image_max_height_tooltip')));
								?>
							</div>
						</div>
						<div class='col-sm-2'>
							<div class='input-group'>
								<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-hdd"></span></span>
								<?php echo form_input ([
									'name' => 'image_max_size',
									'id' => 'image_max_size',
									'class' => 'form-control input-sm required',
									'type' => 'number',
									'min' => 128,
									'max' => 2048,
									'value' => $this->config->get('image_max_size'),
									'data-toggle' => 'tooltip',
									'data-placement' => 'top',
									'title' => lang('Config.image_max_size_tooltip')));
								?>
							</div>
						</div>
						<div class='col-sm-4'>
							<div class='input-group'>
								<span class="input-group-addon input-sm"><?php echo lang('Config.image_allowed_file_types');?></span>
								<?php echo form_multiselect('image_allowed_types[]', $image_allowed_types, $selected_image_allowed_types, [
									'id'=>'image_allowed_types',
									'class'=>'selectpicker show-menu-arrow',
									'data-none-selected-text'=>lang('Common.none_selected_text'),
									'data-selected-text-format'=>'count > 1',
									'data-style'=>'btn-default btn-sm',
									'data-width'=>'100%'));
								?>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.gcaptcha_enable'), 'gcaptcha_enable', ['class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-1'>
					<?php echo form_checkbox ([
						'name' => 'gcaptcha_enable',
						'id' => 'gcaptcha_enable',
						'value' => 'gcaptcha_enable',
						'checked' => $this->config->get('gcaptcha_enable'))); ?>
					&nbsp;
					<label class="control-label">
						<a href="https://www.google.com/recaptcha/admin" target="_blank">
							<span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-placement="right" title="<?php echo lang('Config.gcaptcha_tooltip'); ?>"></span>
						</a>
					</label>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.gcaptcha_site_key'), 'config_gcaptcha_site_key', ['class' => 'required control-label col-xs-2','id' => 'config_gcaptcha_site_key')); ?>
				<div class='col-xs-4'>
					<?php echo form_input ([
						'name' => 'gcaptcha_site_key',
						'id' => 'gcaptcha_site_key',
						'class' => 'form-control input-sm required',
						'value' => $this->config->get('gcaptcha_site_key'))); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.gcaptcha_secret_key'), 'config_gcaptcha_secret_key', ['class' => 'required control-label col-xs-2','id' => 'config_gcaptcha_secret_key')); ?>
				<div class='col-xs-4'>
					<?php echo form_input ([
						'name' => 'gcaptcha_secret_key',
						'id' => 'gcaptcha_secret_key',
						'class' => 'form-control input-sm required',
						'value' => $this->config->get('gcaptcha_secret_key'))); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.suggestions_layout'), 'suggestions_layout', ['class' => 'control-label col-xs-2')); ?>
				<div class="col-sm-10">
					<div class="form-group form-group-sm row">
						<div class='col-sm-3'>
							<div class='input-group'>
								<span class="input-group-addon input-sm"><?php echo lang('Config.suggestions_first_column'); ?></span>
								<?php echo form_dropdown('suggestions_first_column', [
									'name' => lang('Items.name'),
									'item_number' => lang('Items.number_information'),
									'unit_price' => lang('Items.unit_price'),
									'cost_price' => lang('Items.cost_price')),
									$this->config->get('suggestions_first_column'), ['class' => 'form-control input-sm')); ?>
							</div>
						</div>
						<div class='col-sm-3'>
							<div class='input-group'>
								<span class="input-group-addon input-sm"><?php echo lang('Config.suggestions_second_column'); ?></span>
								<?php echo form_dropdown('suggestions_second_column', [
									'' => lang('Config.none'),
									'name' => lang('Items.name'),
									'item_number' => lang('Items.number_information'),
									'unit_price' => lang('Items.unit_price'),
									'cost_price' => lang('Items.cost_price')),
									$this->config->get('suggestions_second_column'), ['class' => 'form-control input-sm')); ?>
							</div>
						</div>
						<div class='col-sm-3'>
							<div class='input-group'>
								<span class="input-group-addon input-sm"><?php echo lang('Config.suggestions_third_column'); ?></span>
								<?php echo form_dropdown('suggestions_third_column', [
									'' => lang('Config.none'),
									'name' => lang('Items.name'),
									'item_number' => lang('Items.number_information'),
									'unit_price' => lang('Items.unit_price'),
									'cost_price' => lang('Items.cost_price')),
									$this->config->get('suggestions_third_column'), ['class' => 'form-control input-sm')); ?>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.giftcard_number'), 'giftcard_number', ['class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-8'>
					<label class="radio-inline">
						<?php echo form_radio ([
							'name' => 'giftcard_number',
							'value' => 'series',
							'checked' => $this->config->get('giftcard_number') == 'series')); ?>
						<?php echo lang('Config.giftcard_series'); ?>
					</label>
					<label class="radio-inline">
						<?php echo form_radio ([
							'name' => 'giftcard_number',
							'value' => 'random',
							'checked' => $this->config->get('giftcard_number') == 'random')); ?>
						<?php echo lang('Config.giftcard_random'); ?>
					</label>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.derive_sale_quantity'), 'derive_sale_quantity', ['class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-1'>
					<?php echo form_checkbox ([
					'name' => 'derive_sale_quantity',
					'id' => 'derive_sale_quantity',
					'value' => 'derive_sale_quantity',
					'checked' => $this->config->get('derive_sale_quantity'))); ?>
					&nbsp
					<label class="control-label">
						<span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-placement="right" title="<?php echo lang('Config.derive_sale_quantity_tooltip'); ?>"></span>
					</label>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.show_office_group'), 'show_office_group', ['class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-1'>
					<?php echo form_checkbox ([
						'name' => 'show_office_group',
						'id' => 'show_office_group',
						'value' => 'show_office_group',
						'checked' => $show_office_group)); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.multi_pack_enabled'), 'multi_pack_enabled', ['class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-1'>
					<?php echo form_checkbox ([
						'name' => 'multi_pack_enabled',
						'id' => 'multi_pack_enabled',
						'value' => 'multi_pack_enabled',
						'checked' => $this->config->get('multi_pack_enabled'))); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.include_hsn'), 'include_hsn', ['class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-1'>
					<?php echo form_checkbox ([
						'name' => 'include_hsn',
						'id' => 'include_hsn',
						'value' => 'include_hsn',
						'checked' => $this->config->get('include_hsn'))); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.category_dropdown'), 'category_dropdown', ['class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-1'>
					<?php echo form_checkbox ([
						'name' => 'category_dropdown',
						'id' => 'category_dropdown',
						'value' => 'category_dropdown',
						'checked' => $this->config->get('category_dropdown'))); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.backup_database'), 'config_backup_database', ['class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<div id="backup_db" class="btn btn-default btn-sm">
						<span style="top:22%;"><?php echo lang('Config.backup_button'); ?></span>
					</div>
				</div>
			</div>

			<?php echo form_submit ([
				'name' => 'submit_general',
				'id' => 'submit_general',
				'value' => lang('Common.submit'),
				'class' => 'btn btn-primary btn-sm pull-right')); ?>
		</fieldset>
	</div>
<?php echo form_close(); ?>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{
	var enable_disable_gcaptcha_enable = (function() {
		var gcaptcha_enable = $("#gcaptcha_enable").is(":checked");
		if(gcaptcha_enable)
		{
			$("#gcaptcha_site_key, #gcaptcha_secret_key").prop("disabled", !gcaptcha_enable).addClass("required");
			$("#config_gcaptcha_site_key, #config_gcaptcha_secret_key").addClass("required");
		}
		else
		{
			$("#gcaptcha_site_key, #gcaptcha_secret_key").prop("disabled", gcaptcha_enable).removeClass("required");
			$("#config_gcaptcha_site_key, #config_gcaptcha_secret_key").removeClass("required");
		}

		return arguments.callee;
	})();

	$("#gcaptcha_enable").change(enable_disable_gcaptcha_enable);

	$("#backup_db").click(function() {
		window.location='<?php echo site_url('config/backup_db') ?>';
	});

	$('#general_config_form').validate($.extend(form_support.handler, {

		errorLabelContainer: "#general_error_message_box",

		rules:
		{
			lines_per_page:
			{
				required: true,
				remote: "<?php echo site_url($controller_name . '/check_numeric')?>"
			},
			default_sales_discount:
			{
				required: true,
				remote: "<?php echo site_url($controller_name . '/check_numeric')?>"
			},
			gcaptcha_site_key:
			{
				required: "#gcaptcha_enable:checked"
			},
			gcaptcha_secret_key:
			{
				required: "#gcaptcha_enable:checked"
			}
		},

		messages:
		{
			default_sales_discount:
			{
				required: "<?php echo lang('Config.default_sales_discount_required'); ?>",
				number: "<?php echo lang('Config.default_sales_discount_number'); ?>"
			},
			lines_per_page:
			{
				required: "<?php echo lang('Config.lines_per_page_required'); ?>",
				number: "<?php echo lang('Config.lines_per_page_number'); ?>"
			},
			gcaptcha_site_key:
			{
				required: "<?php echo lang('Config.gcaptcha_site_key_required'); ?>"
			},
			gcaptcha_secret_key:
			{
				required: "<?php echo lang('Config.gcaptcha_secret_key_required'); ?>"
			}
		},

		submitHandler: function(form) {
			$(form).ajaxSubmit({
				beforeSerialize: function(arr, $form, options) {
					$("#gcaptcha_site_key, #gcaptcha_secret_key").prop("disabled", false);
					return true;
				},
				success: function(response) {
					$.notify( { message: response.message }, { type: response.success ? 'success' : 'danger'} )
					// set back disabled state
					enable_disable_gcaptcha_enable();
				},
				dataType: 'json'
			});
		}
	}));
});
</script>

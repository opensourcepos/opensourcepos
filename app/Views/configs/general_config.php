<?php
/**
 * @var array $themes
 * @var array $image_allowed_types
 * @var array $selected_image_allowed_types
 * @var bool $show_office_group
 * @var string $controller_name
 * @var array $config
 */
?>
<?= form_open('config/saveGeneral/', ['id' => 'general_config_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal']) ?>
	<div id="config_wrapper">
		<fieldset id="config_info">
			<div id="required_fields_message"><?= lang('Common.fields_required_message') ?></div>
			<ul id="general_error_message_box" class="error_message_box"></ul>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.theme'), 'theme', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-sm-10'>
					<div class="form-group form-group-sm row">
						<div class='col-sm-3'>
							<?= form_dropdown(
								'theme',
								$themes,
								$config['theme'],
								"class='form-control input-sm' id='theme-change'"
							) ?>
						</div>
						<div class="col-sm-7">
							<a href="<?= 'https://bootswatch.com/3/' . ('bootstrap' == ($config['theme']) ? 'default' : esc($config['theme'])) ?>" target="_blank" rel=”noopener”>
								<span><?= lang('Config.theme_preview') . ' ' . ucfirst(esc($config['theme'])) . ' ' ?></span><span class="glyphicon glyphicon-new-window"></span>
							</a>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.login_form'), 'login_form', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?= form_dropdown(
							'login_form',
							[
								'floating_labels' => lang('Config.floating_labels'),
								'input_groups' => lang('Config.input_groups')
							],
							$config['login_form'],
							"class='form-control input-sm'"
						) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.default_sales_discount'), 'default_sales_discount', ['class' => 'control-label col-xs-2 required']) ?>
				<div class='col-xs-2'>
					<div class="input-group">
						<?= form_input ([
							'name' => 'default_sales_discount',
							'id' => 'default_sales_discount',
							'class' => 'form-control input-sm required',
							'type' => 'number',
							'min' => 0,
							'max' => 100,
							'value' => $config['default_sales_discount']
							]) ?>
						<span class="input-group-btn">
							<?= form_checkbox ([
								'id' => 'default_sales_discount_type',
								'name' => 'default_sales_discount_type',
								'value' => 1,
								'data-toggle' => 'toggle',
								'data-size' => 'normal',
								'data-onstyle' => 'success',
								'data-on' => '<b>' . esc($config['currency_symbol']).'</b>',
								'data-off' => '<b>%</b>',
								'checked' => $config['default_sales_discount_type'] == 1
								]) ?>
						</span>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.default_receivings_discount'), 'default_receivings_discount', ['class' => 'control-label col-xs-2 required']) ?>
				<div class='col-xs-2'>
					<div class="input-group">
						<?= form_input ([
							'name' => 'default_receivings_discount',
							'id' => 'default_receivings_discount',
							'class' => 'form-control input-sm required',
							'type' => 'number',
							'min' => 0,
							'max' => 100,
							'value' => $config['default_receivings_discount']
							]) ?>
						<span class="input-group-btn">
							<?= form_checkbox ([
								'id' => 'default_receivings_discount_type',
								'name' => 'default_receivings_discount_type',
								'value' => 1,
								'data-toggle' => 'toggle',
								'data-size' => 'normal',
								'data-onstyle' => 'success',
								'data-on' => '<b>' . esc($config['currency_symbol']) . '</b>',
								'data-off' => '<b>%</b>',
								'checked' => $config['default_receivings_discount_type'] == 1
								]) ?>
						</span>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.enforce_privacy'), 'enforce_privacy', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-1'>
					<?= form_checkbox ([
						'name' => 'enforce_privacy',
						'id' => 'enforce_privacy',
						'value' => 'enforce_privacy',
						'checked' => $config['enforce_privacy'] == 1
					]) ?>
					&nbsp
					<label class="control-label">
						<span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-placement="right" title="<?= lang('Config.enforce_privacy_tooltip') ?>"></span>
					</label>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.receiving_calculate_average_price'), 'receiving_calculate_average_price', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-1'>
					<?= form_checkbox ([
						'name' => 'receiving_calculate_average_price',
						'id' => 'receiving_calculate_average_price',
						'value' => 'receiving_calculate_average_price',
						'checked' => $config['receiving_calculate_average_price'] == 1
						]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.lines_per_page'), 'lines_per_page', ['class' => 'control-label col-xs-2 required']) ?>
				<div class='col-xs-2'>
					<?= form_input ([
						'name' => 'lines_per_page',
						'id' => 'lines_per_page',
						'class' => 'form-control input-sm required',
						'type' => 'number',
						'min' => 10,
						'max' => 1000,
						'value' => $config['lines_per_page']
						]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.notify_alignment'), 'notify_horizontal_position', ['class' => 'control-label col-xs-2']) ?>
				<div class="col-sm-10">
					<div class="form-group form-group-sm row">
						<div class='col-sm-2'>
							<?= form_dropdown(
								'notify_vertical_position',
								[
									'top' => lang('Config.top'),
									'bottom' => lang('Config.bottom')
								],
								$config['notify_vertical_position'],
								"class='form-control input-sm'"
							) ?>
						</div>
						<div class='col-sm-2'>
							<?= form_dropdown(
								'notify_horizontal_position',
								[
									'left' => lang('Config.left'),
									'center' => lang('Config.center'),
									'right' => lang('Config.right')
								],
								$config['notify_horizontal_position'],
								"class='form-control input-sm'"
							) ?>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.image_restrictions'), 'image_restrictions', ['class' => 'control-label col-xs-2']) ?>
				<div class="col-sm-10">
					<div class="form-group form-group-sm row">
						<div class='col-sm-2'>
							<div class='input-group'>
								<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-resize-horizontal"></span></span>
								<?= form_input ([
									'name' => 'image_max_width',
									'id' => 'image_max_width',
									'class' => 'form-control input-sm required',
									'type' => 'number',
									'min' => 128,
									'max' => 3840,
									'value' => $config['image_max_width'],
									'data-toggle' => 'tooltip',
									'data-placement' => 'top',
									'title' => lang('Config.image_max_width_tooltip')
								]) ?>
							</div>
						</div>
						<div class='col-sm-2'>
							<div class='input-group'>
								<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-resize-vertical"></span></span>
								<?= form_input ([
									'name' => 'image_max_height',
									'id' => 'image_max_height',
									'class' => 'form-control input-sm required',
									'type' => 'number',
									'min' => 128,
									'max' => 3840,
									'value' => $config['image_max_height'],
									'data-toggle' => 'tooltip',
									'data-placement' => 'top',
									'title' => lang('Config.image_max_height_tooltip')
								])	?>
							</div>
						</div>
						<div class='col-sm-2'>
							<div class='input-group'>
								<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-hdd"></span></span>
								<?= form_input ([
									'name' => 'image_max_size',
									'id' => 'image_max_size',
									'class' => 'form-control input-sm required',
									'type' => 'number',
									'min' => 128,
									'max' => 2048,
									'value' => $config['image_max_size'],
									'data-toggle' => 'tooltip',
									'data-placement' => 'top',
									'title' => lang('Config.image_max_size_tooltip')
								]) ?>
							</div>
						</div>
						<div class='col-sm-4'>
							<div class='input-group'>
								<span class="input-group-addon input-sm"><?= lang('Config.image_allowed_file_types') ?></span>
								<?= form_multiselect([
									'name' => 'image_allowed_types',
									'options' => $image_allowed_types,
									'selected' => $selected_image_allowed_types,
									'extra' => "id='image_allowed_types'".
										"class='selectpicker show-menu-arrow'".
										"data-none-selected-text=".lang('Common.none_selected_text').
										"data-selected-text-format='count > 1'".
										"data-style='btn-default btn-sm'".
										"data-width='100%'"
								]) ?>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.gcaptcha_enable'), 'gcaptcha_enable', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-1'>
					<?= form_checkbox ([
						'name' => 'gcaptcha_enable',
						'id' => 'gcaptcha_enable',
						'value' => 'gcaptcha_enable',
						'checked' => $config['gcaptcha_enable'] == 1
					]) ?>
					<label class="control-label">
						<a href="https://www.google.com/recaptcha/admin" target="_blank">
							<span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-placement="right" title="<?= lang('Config.gcaptcha_tooltip') ?>"></span>
						</a>
					</label>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.gcaptcha_site_key'), 'config_gcaptcha_site_key', ['class' => 'required control-label col-xs-2','id' => 'config_gcaptcha_site_key']) ?>
				<div class='col-xs-4'>
					<?= form_input ([
						'name' => 'gcaptcha_site_key',
						'id' => 'gcaptcha_site_key',
						'class' => 'form-control input-sm required',
						'value' => $config['gcaptcha_site_key']
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.gcaptcha_secret_key'), 'config_gcaptcha_secret_key', ['class' => 'required control-label col-xs-2','id' => 'config_gcaptcha_secret_key']) ?>
				<div class='col-xs-4'>
					<?= form_input ([
						'name' => 'gcaptcha_secret_key',
						'id' => 'gcaptcha_secret_key',
						'class' => 'form-control input-sm required',
						'value' => $config['gcaptcha_secret_key']
						]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.suggestions_layout'), 'suggestions_layout', ['class' => 'control-label col-xs-2']) ?>
				<div class="col-sm-10">
					<div class="form-group form-group-sm row">
						<div class='col-sm-3'>
							<div class='input-group'>
								<span class="input-group-addon input-sm"><?= lang('Config.suggestions_first_column') ?></span>
								<?= form_dropdown(
									'suggestions_first_column',
									[
										'name' => lang('Items.name'),
										'item_number' => lang('Items.number_information'),
										'unit_price' => lang('Items.unit_price'),
										'cost_price' => lang('Items.cost_price')
									],
									$config['suggestions_first_column'],
									"class='form-control input-sm'"
								) ?>
							</div>
						</div>
						<div class='col-sm-3'>
							<div class='input-group'>
								<span class="input-group-addon input-sm"><?= lang('Config.suggestions_second_column') ?></span>
								<?= form_dropdown(
									'suggestions_second_column',
									[
										'' => lang('Config.none'),
										'name' => lang('Items.name'),
										'item_number' => lang('Items.number_information'),
										'unit_price' => lang('Items.unit_price'),
										'cost_price' => lang('Items.cost_price')
									],
									$config['suggestions_second_column'],
									"class='form-control input-sm'"
								) ?>
							</div>
						</div>
						<div class='col-sm-3'>
							<div class='input-group'>
								<span class="input-group-addon input-sm"><?= lang('Config.suggestions_third_column') ?></span>
								<?= form_dropdown(
									'suggestions_third_column',
									[
										'' => lang('Config.none'),
										'name' => lang('Items.name'),
										'item_number' => lang('Items.number_information'),
										'unit_price' => lang('Items.unit_price'),
										'cost_price' => lang('Items.cost_price')
									],
									$config['suggestions_third_column'],
									"class='form-control input-sm'"
								) ?>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.giftcard_number'), 'giftcard_number', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-8'>
					<label class="radio-inline">
						<?= form_radio ([
							'name' => 'giftcard_number',
							'value' => 'series',
							'checked' => $config['giftcard_number'] == 'series']) ?>
						<?= lang('Config.giftcard_series') ?>
					</label>
					<label class="radio-inline">
						<?= form_radio ([
							'name' => 'giftcard_number',
							'value' => 'random',
							'checked' => $config['giftcard_number'] == 'random']) ?>
						<?= lang('Config.giftcard_random') ?>
					</label>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.derive_sale_quantity'), 'derive_sale_quantity', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-1'>
					<?= form_checkbox ([
					'name' => 'derive_sale_quantity',
					'id' => 'derive_sale_quantity',
					'value' => 'derive_sale_quantity',
					'checked' => $config['derive_sale_quantity'] == 1
					]) ?>
					&nbsp
					<label class="control-label">
						<span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-placement="right" title="<?= lang('Config.derive_sale_quantity_tooltip') ?>"></span>
					</label>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.show_office_group'), 'show_office_group', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-1'>
					<?= form_checkbox ([
						'name' => 'show_office_group',
						'id' => 'show_office_group',
						'value' => 'show_office_group',
						'checked' => $show_office_group == 1
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.multi_pack_enabled'), 'multi_pack_enabled', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-1'>
					<?= form_checkbox ([
						'name' => 'multi_pack_enabled',
						'id' => 'multi_pack_enabled',
						'value' => 'multi_pack_enabled',
						'checked' => $config['multi_pack_enabled'] == 1
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.include_hsn'), 'include_hsn', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-1'>
					<?= form_checkbox ([
						'name' => 'include_hsn',
						'id' => 'include_hsn',
						'value' => 'include_hsn',
						'checked' => $config['include_hsn'] == 1
						]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.category_dropdown'), 'category_dropdown', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-1'>
					<?= form_checkbox ([
						'name' => 'category_dropdown',
						'id' => 'category_dropdown',
						'value' => 'category_dropdown',
						'checked' => $config['category_dropdown'] == 1
					]) ?>
				</div>
			</div>

			<?= form_submit ([
				'name' => 'submit_general',
				'id' => 'submit_general',
				'value' => lang('Common.submit'),
				'class' => 'btn btn-primary btn-sm pull-right']) ?>
		</fieldset>
	</div>
<?= form_close() ?>

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

	$('#general_config_form').validate($.extend(form_support.handler, {

		errorLabelContainer: "#general_error_message_box",

		rules:
		{
			lines_per_page:
			{
				required: true,
				remote: "<?= "$controller_name/checkNumeric" ?>"
			},
			default_sales_discount:
			{
				required: true,
				remote: "<?= "$controller_name/checkNumeric" ?>"
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
				required: "<?= lang('Config.default_sales_discount_required') ?>",
				number: "<?= lang('Config.default_sales_discount_number') ?>"
			},
			lines_per_page:
			{
				required: "<?= lang('Config.lines_per_page_required') ?>",
				number: "<?= lang('Config.lines_per_page_number') ?>"
			},
			gcaptcha_site_key:
			{
				required: "<?= lang('Config.gcaptcha_site_key_required') ?>"
			},
			gcaptcha_secret_key:
			{
				required: "<?= lang('Config.gcaptcha_secret_key_required') ?>"
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

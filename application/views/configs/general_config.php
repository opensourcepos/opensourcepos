<?php echo form_open('config/save_general/', array('id' => 'general_config_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal')); ?>
	<div id="config_wrapper">
		<fieldset id="config_info">
			<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
			<ul id="general_error_message_box" class="error_message_box"></ul>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_theme'), 'theme', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_dropdown('theme', $themes, $this->config->item('theme'), array('class' => 'form-control input-sm')); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_default_sales_discount'), 'default_sales_discount', array('class' => 'control-label col-xs-2 required')); ?>
				<div class='col-xs-2'>
					<div class="input-group">
						<?php echo form_input(array(
							'name' => 'default_sales_discount',
							'id' => 'default_sales_discount',
							'class' => 'form-control input-sm required',
							'type' => 'number',
							'min' => 0,
							'max' => 100,
							'value' => $this->config->item('default_sales_discount'))); ?>
						<span class="input-group-btn">
							<?php echo form_checkbox(array(
								'id' => 'default_sales_discount_type', 
								'name' => 'default_sales_discount_type', 
								'value' => 1, 
								'data-toggle' => 'toggle',
								'data-size' => 'normal', 
								'data-onstyle' => 'success', 
								'data-on' => '<b>'.$this->config->item('currency_symbol').'</b>', 
								'data-off' => '<b>%</b>', 
								'checked' => $this->config->item('default_sales_discount_type'))); ?>
						</span>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_default_receivings_discount'), 'default_receivings_discount', array('class' => 'control-label col-xs-2 required')); ?>
				<div class='col-xs-2'>
					<div class="input-group">
						<?php echo form_input(array(
							'name' => 'default_receivings_discount',
							'id' => 'default_receivings_discount',
							'class' => 'form-control input-sm required',
							'type' => 'number',
							'min' => 0,
							'max' => 100,
							'value' => $this->config->item('default_receivings_discount'))); ?>
						<span class="input-group-btn">
							<?php echo form_checkbox(array(
								'id' => 'default_receivings_discount_type',
								'name' => 'default_receivings_discount_type',
								'value' => 1,
								'data-toggle' => 'toggle',
								'data-size' => 'normal',
								'data-onstyle' => 'success',
								'data-on' => '<b>'.$this->config->item('currency_symbol').'</b>',
								'data-off' => '<b>%</b>',
								'checked' => $this->config->item('default_receivings_discount_type'))); ?>
						</span>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_enforce_privacy'), 'enforce_privacy', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-1'>
					<?php echo form_checkbox(array(
						'name' => 'enforce_privacy',
						'id' => 'enforce_privacy',
						'value' => 'enforce_privacy',
						'checked' => $this->config->item('enforce_privacy'))); ?>
					&nbsp
					<label class="control-label">
						<span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-placement="right" title="<?php echo $this->lang->line('config_enforce_privacy_tooltip'); ?>"></span>
					</label>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_receiving_calculate_average_price'), 'receiving_calculate_average_price', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-1'>
					<?php echo form_checkbox(array(
						'name' => 'receiving_calculate_average_price',
						'id' => 'receiving_calculate_average_price',
						'value' => 'receiving_calculate_average_price',
						'checked' => $this->config->item('receiving_calculate_average_price'))); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_lines_per_page'), 'lines_per_page', array('class' => 'control-label col-xs-2 required')); ?>
				<div class='col-xs-2'>
					<?php echo form_input(array(
						'name' => 'lines_per_page',
						'id' => 'lines_per_page',
						'class' => 'form-control input-sm required',
						'type' => 'number',
						'min' => 10,
						'max' => 1000,
						'value' => $this->config->item('lines_per_page'))); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_notify_alignment'), 'notify_horizontal_position', array('class' => 'control-label col-xs-2')); ?>
				<div class="col-sm-10">
					<div class="form-group form-group-sm row">
						<div class='col-sm-2'>
							<?php echo form_dropdown('notify_vertical_position', array(
									'top' => $this->lang->line('config_top'),
									'bottom' => $this->lang->line('config_bottom')
								),
								$this->config->item('notify_vertical_position'), array('class' => 'form-control input-sm')); ?>
						</div>
						<div class='col-sm-2'>
							<?php echo form_dropdown('notify_horizontal_position', array(
									'left' => $this->lang->line('config_left'),
									'center' => $this->lang->line('config_center'),
									'right' => $this->lang->line('config_right')
								),
								$this->config->item('notify_horizontal_position'), array('class' => 'form-control input-sm')); ?>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_gcaptcha_enable'), 'gcaptcha_enable', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-1'>
					<?php echo form_checkbox(array(
						'name' => 'gcaptcha_enable',
						'id' => 'gcaptcha_enable',
						'value' => 'gcaptcha_enable',
						'checked' => $this->config->item('gcaptcha_enable'))); ?>
					&nbsp
					<label class="control-label">
						<a href="https://www.google.com/recaptcha/admin" target="_blank">
							<span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-placement="right" title="<?php echo $this->lang->line('config_gcaptcha_tooltip'); ?>"></span>
						</a>
					</label>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_gcaptcha_site_key'), 'config_gcaptcha_site_key', array('class' => 'required control-label col-xs-2','id' => 'config_gcaptcha_site_key')); ?>
				<div class='col-xs-4'>
					<?php echo form_input(array(
						'name' => 'gcaptcha_site_key',
						'id' => 'gcaptcha_site_key',
						'class' => 'form-control input-sm required',
						'value' => $this->config->item('gcaptcha_site_key'))); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_gcaptcha_secret_key'), 'config_gcaptcha_secret_key', array('class' => 'required control-label col-xs-2','id' => 'config_gcaptcha_secret_key')); ?>
				<div class='col-xs-4'>
					<?php echo form_input(array(
						'name' => 'gcaptcha_secret_key',
						'id' => 'gcaptcha_secret_key',
						'class' => 'form-control input-sm required',
						'value' => $this->config->item('gcaptcha_secret_key'))); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_suggestions_layout'), 'suggestions_layout', array('class' => 'control-label col-xs-2')); ?>
				<div class="col-sm-10">
					<div class="form-group form-group-sm row">
						<label class="control-label col-sm-1"><?php echo $this->lang->line('config_suggestions_first_column').' '; ?></label>
						<div class='col-sm-2'>
							<?php echo form_dropdown('suggestions_first_column', array(
								'name' => $this->lang->line('items_name'),
								'item_number' => $this->lang->line('items_number_information'),
								'unit_price' => $this->lang->line('items_unit_price')
							),
							$this->config->item('suggestions_first_column'), array('class' => 'form-control input-sm')); ?>
						</div>
						<label class="control-label col-sm-1"><?php echo $this->lang->line('config_suggestions_second_column').' '; ?></label>
						<div class='col-sm-2'>
							<?php echo form_dropdown('suggestions_second_column', array(
									'' => $this->lang->line('config_none'),
									'name' => $this->lang->line('items_name'),
									'item_number' => $this->lang->line('items_number_information'),
									'unit_price' => $this->lang->line('items_unit_price')
							),
							$this->config->item('suggestions_second_column'), array('class' => 'form-control input-sm')); ?>
						</div>
						<label class="control-label col-sm-1"><?php echo $this->lang->line('config_suggestions_third_column').' '; ?></label>
						<div class='col-sm-2'>
							<?php echo form_dropdown('suggestions_third_column', array(
									'' => $this->lang->line('config_none'),
									'name' => $this->lang->line('items_name'),
									'item_number' => $this->lang->line('items_number_information'),
									'unit_price' => $this->lang->line('items_unit_price')
						),
							$this->config->item('suggestions_third_column'), array('class' => 'form-control input-sm')); ?>
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
							'checked' => $this->config->item('giftcard_number') == 'series')); ?>
						<?php echo $this->lang->line('config_giftcard_series'); ?>
					</label>
					<label class="radio-inline">
						<?php echo form_radio(array(
							'name' => 'giftcard_number',
							'value' => 'random',
							'checked' => $this->config->item('giftcard_number') == 'random')); ?>
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
					'checked' => $this->config->item('derive_sale_quantity'))); ?>
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
						'checked' => $show_office_group)); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_multi_pack_enabled'), 'multi_pack_enabled', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-1'>
					<?php echo form_checkbox(array(
						'name' => 'multi_pack_enabled',
						'id' => 'multi_pack_enabled',
						'value' => 'multi_pack_enabled',
						'checked' => $this->config->item('multi_pack_enabled'))); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_include_hsn'), 'include_hsn', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-1'>
					<?php echo form_checkbox(array(
						'name' => 'include_hsn',
						'id' => 'include_hsn',
						'value' => 'include_hsn',
						'checked' => $this->config->item('include_hsn'))); ?>
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

			<?php echo form_submit(array(
				'name' => 'submit_general',
				'id' => 'submit_general',
				'value' => $this->lang->line('common_submit'),
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
				required: "<?php echo $this->lang->line('config_default_sales_discount_required'); ?>",
				number: "<?php echo $this->lang->line('config_default_sales_discount_number'); ?>"
			},
			lines_per_page:
			{
				required: "<?php echo $this->lang->line('config_lines_per_page_required'); ?>",
				number: "<?php echo $this->lang->line('config_lines_per_page_number'); ?>"
			},
			gcaptcha_site_key:
			{
				required: "<?php echo $this->lang->line('config_gcaptcha_site_key_required'); ?>"
			},
			gcaptcha_secret_key:
			{
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
					$.notify(response.message, { type: response.success ? 'success' : 'danger'} );
					// set back disabled state
					enable_disable_gcaptcha_enable();
				},
				dataType: 'json'
			});
		}
	}));
});
</script>

<?php echo form_open('config/save_locale/', array('id' => 'locale_config_form', 'class' => 'form-horizontal')); ?>
	<div id="config_wrapper">
		<fieldset id="config_info">
			<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
			<ul id="locale_error_message_box" class="error_message_box"></ul>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_number_locale'), 'number_locale', array('class' => 'control-label col-xs-2')); ?>
				<div class='row'>
					<div class='col-xs-1'>
						<?php echo form_input('number_locale', $this->config->item('number_locale'), array('class' => 'form-control input-sm', 'id' => 'number_locale')); ?>
						<?php echo form_hidden('save_number_locale', $this->config->item('number_locale')); ?>
					</div>
					<div class="col-xs-2">
						<label class="control-label">
							<a href="https://github.com/opensourcepos/opensourcepos/wiki/Localisation-support" target="_blank">
								<span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-placement="right" title="<?php echo $this->lang->line('config_number_locale_tooltip'); ?>"></span>
							</a>
							<span id="number_locale_example">
								&nbsp&nbsp<?php echo to_currency(1234567890.12300); ?>
							</span>
						</label>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_thousands_separator'), 'thousands_separator', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_checkbox(array(
						'name' => 'thousands_separator',
						'id' => 'thousands_separator',
						'value' => 'thousands_separator',
						'checked'=>$this->config->item('thousands_separator'))); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_currency_symbol'), 'currency_symbol', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-1'>
					<?php echo form_input(array(
						'name' => 'currency_symbol',
						'id' => 'currency_symbol',
						'class' => 'form-control input-sm number_locale',
						'value'=>$this->config->item('currency_symbol'))); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_currency_code'), 'currency_code', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-1'>
					<?php echo form_input(array(
						'name' => 'currency_code',
						'id' => 'currency_code',
						'class' => 'form-control input-sm number_locale',
						'value'=>$currency_code)); ?>
				</div>
			</div>
			
			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_currency_decimals'), 'currency_decimals', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_dropdown('currency_decimals', array(
						'0' => '0',
						'1' => '1',
						'2' => '2'
					),
					$this->config->item('currency_decimals'), array('class' => 'form-control input-sm'));
					?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_tax_decimals'), 'tax_decimals', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_dropdown('tax_decimals', array(
						'0' => '0',
						'1' => '1',
						'2' => '2',
						'3' => '3',
						'4' => '4'
					),
					$this->config->item('tax_decimals'), array('class' => 'form-control input-sm'));
					?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_quantity_decimals'), 'quantity_decimals', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_dropdown('quantity_decimals', array(
						'0' => '0',
						'1' => '1',
						'2' => '2',
						'3' => '3'
					),
					$this->config->item('quantity_decimals'), array('class' => 'form-control input-sm'));
					?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_cash_decimals'), 'cash_decimals', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_dropdown('cash_decimals', array(
						'-1' => '-1',
						'0' => '0',
						'1' => '1',
						'2' => '2'
					),
						$this->config->item('cash_decimals'), array('class' => 'form-control input-sm'));
					?>
				</div>
				<div class='col-xs-1'>
					<label class="control-label">
						<span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-placement="right" title="<?php echo $this->lang->line('config_cash_decimals_tooltip'); ?>"></span>
					</label>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_cash_rounding'), 'cash_rounding_code', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_dropdown('cash_rounding_code', $rounding_options, $this->config->item('cash_rounding_code'), array('class' => 'form-control input-sm'));
					?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_payment_options_order'), 'payment_options_order', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-4'>
					<?php echo form_dropdown('payment_options_order', array(
						'cashdebitcredit' => $this->lang->line('sales_cash') . ' / ' . $this->lang->line('sales_debit') . ' / ' . $this->lang->line('sales_credit'),
						'debitcreditcash' => $this->lang->line('sales_debit') . ' / ' . $this->lang->line('sales_credit') . ' / ' . $this->lang->line('sales_cash'),
						'debitcashcredit' => $this->lang->line('sales_debit') . ' / ' . $this->lang->line('sales_cash') . ' / ' . $this->lang->line('sales_credit'),
						'creditdebitcash' => $this->lang->line('sales_credit') . ' / ' . $this->lang->line('sales_debit') . ' / ' . $this->lang->line('sales_cash'),
						'creditcashdebit' => $this->lang->line('sales_credit') . ' / ' . $this->lang->line('sales_cash') . ' / ' . $this->lang->line('sales_debit')
					),
					$this->config->item('payment_options_order'), array('class' => 'form-control input-sm'));
					?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_country_codes'), 'country_codes', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-1'>
					<?php echo form_input('country_codes', $this->config->item('country_codes'), array('class' => 'form-control input-sm')); ?>
				</div>
				<div class="col-xs-1">
					<label class="control-label">
						<a href="http://wiki.openstreetmap.org/wiki/Nominatim/Country_Codes" target="_blank"><span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-placement="right" title="<?php echo $this->lang->line('config_country_codes_tooltip'); ?>"></span></a>
					</label>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_language'), 'language', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-4'>
					<?php echo form_dropdown(
							'language',
							get_languages(),
							current_language_code(TRUE) . ':' . current_language(TRUE),
							array('class' => 'form-control input-sm')
						);
					?>
				</div>
			</div>

			<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('config_timezone'), 'timezone', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-4'>
				<?php echo form_dropdown(
					'timezone',
					get_timezones(),
					$this->config->item('timezone') ? $this->config->item('timezone') : date_default_timezone_get(), array('class' => 'form-control input-sm'));
					?>
				</div>
			</div>

			<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('config_datetimeformat'), 'datetimeformat', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-sm-2'>
				<?php echo form_dropdown('dateformat',
					get_dateformats(),
					$this->config->item('dateformat'), array('class' => 'form-control input-sm'));
					?>
				</div>
				<div class='col-sm-2'>
				<?php echo form_dropdown('timeformat',
					get_timeformats(),
					$this->config->item('timeformat'), array('class' => 'form-control input-sm'));
					?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_date_or_time_format'), 'date_or_time_format', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_checkbox(array(
						'name' => 'date_or_time_format',
						'id' => 'date_or_time_format',
						'value' => 'date_or_time_format',
						'checked'=>$this->config->item('date_or_time_format'))); ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_financial_year'), 'financial_year', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_dropdown('financial_year', array(
						'1' => $this->lang->line('config_financial_year_jan'),
						'2' => $this->lang->line('config_financial_year_feb'),
						'3' => $this->lang->line('config_financial_year_mar'),
						'4' => $this->lang->line('config_financial_year_apr'),
						'5' => $this->lang->line('config_financial_year_may'),
						'6' => $this->lang->line('config_financial_year_jun'),
						'7' => $this->lang->line('config_financial_year_jul'),
						'8' => $this->lang->line('config_financial_year_aug'),
						'9' => $this->lang->line('config_financial_year_sep'),
						'10' => $this->lang->line('config_financial_year_oct'),
						'11' => $this->lang->line('config_financial_year_nov'),
						'12' => $this->lang->line('config_financial_year_dec')
					),
					$this->config->item('financial_year'), array('class' => 'form-control input-sm'));
					?>
				</div>
			</div>

			<?php echo form_submit(array(
				'name' => 'submit_locale',
				'id' => 'submit_locale',
				'value' => $this->lang->line('common_submit'),
				'class' => 'btn btn-primary btn-sm pull-right')); ?>
		</fieldset>
	</div>
<?php echo form_close(); ?>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{
	$('span').tooltip();

	$('#currency_symbol, #thousands_separator, #currency_code').change(function() {
		var data = { number_locale: $('#number_locale').val() };
		data['save_number_locale'] = $("input[name='save_number_locale']").val();
		data['currency_symbol'] = $('#currency_symbol').val();
		data['currency_code'] = $('#currency_code').val();
		data['thousands_separator'] = $('#thousands_separator').is(":checked")
		$.post("<?php echo site_url($controller_name . '/ajax_check_number_locale')?>",
			data,
			function(response) {
				$("input[name='save_number_locale']").val(response.save_number_locale);
				$('#number_locale_example').text(response.number_locale_example);
				$('#currency_symbol').val(response.currency_symbol);
				$('#currency_code').val(response.currency_code);
			},
			'json'
		);
	});

	$('#locale_config_form').validate($.extend(form_support.handler, {
		rules:
		{
			number_locale:
			{
				required: true,
				remote:
				{
					url: "<?php echo site_url($controller_name . '/ajax_check_number_locale')?>",
					type: 'POST',
					data: {
						'number_locale': function() { return $('#number_locale').val(); },
						'save_number_locale': function() { return $("input[name='save_number_locale']").val(); },
						'currency_symbol': function() { return $('#currency_symbol').val(); },
						'thousands_separator': function() { return $('#thousands_separator').is(':checked'); },
						'currency_code': function() { return $('#currency_code').val(); }
					},
					dataFilter: function(data) {
						var response = JSON.parse(data);
						$("input[name='save_number_locale']").val(response.save_number_locale);
						$('#number_locale_example').text(response.number_locale_example);
						$('#currency_symbol').val(response.currency_symbol);
						$('#currency_code').val(response.currency_code);
						return response.success;
					}
				}
			}
		},

		messages:
		{
			number_locale: {
				required: "<?php echo $this->lang->line('config_number_locale_required') ?>",
				number_locale: "<?php echo $this->lang->line('config_number_locale_invalid') ?>"
			}
		},

		errorLabelContainer: '#locale_error_message_box'
	}));
});
</script>

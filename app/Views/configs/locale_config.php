<?php
/**
 * @var string $currency_code
 * @var array $rounding_options
 * @var string $controller_name
 */
?>

<?php echo form_open('config/save_locale/', ['id' => 'locale_config_form', 'class' => 'form-horizontal']) ?>
	<div id="config_wrapper">
		<fieldset id="config_info">
			<div id="required_fields_message"><?php echo lang('Common.fields_required_message') ?></div>
			<ul id="locale_error_message_box" class="error_message_box"></ul>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.number_locale'), 'number_locale', ['class' => 'control-label col-xs-2']) ?>
				<div class='row'>
					<div class='col-xs-1'>
						<?php echo form_input('number_locale', esc($config['number_locale'], 'attr'), ['class' => 'form-control input-sm', 'id' => 'number_locale']) ?>
						<?php echo form_hidden('save_number_locale', esc($config['number_locale'], 'attr')) ?>
					</div>
					<div class="col-xs-2">
						<label class="control-label">
							<a href="https://github.com/opensourcepos/opensourcepos/wiki/Localisation-support" target="_blank">
								<span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-placement="right" title="<?php echo lang('Config.number_locale_tooltip') ?>"></span>
							</a>
							<span id="number_locale_example">
								&nbsp&nbsp<?php echo to_currency(1234567890.12300) ?>
							</span>
						</label>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.thousands_separator'), 'thousands_separator', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?php echo form_checkbox ([
						'name' => 'thousands_separator',
						'id' => 'thousands_separator',
						'value' => 'thousands_separator',
						'checked' => $config['thousands_separator']
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.currency_symbol'), 'currency_symbol', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-1'>
					<?php echo form_input ([
						'name' => 'currency_symbol',
						'id' => 'currency_symbol',
						'class' => 'form-control input-sm number_locale',
						'value' => esc($config['currency_symbol'], 'attr')
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.currency_code'), 'currency_code', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-1'>
					<?php echo form_input ([
						'name' => 'currency_code',
						'id' => 'currency_code',
						'class' => 'form-control input-sm number_locale',
						'value' => esc($currency_code, 'attr')
					]) ?>
				</div>
			</div>
			
			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.currency_decimals'), 'currency_decimals', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?php echo form_dropdown(
						'currency_decimals',
						[
							'0' => '0',
							'1' => '1',
							'2' => '2'
						],
						$config['currency_decimals'],
						['class' => 'form-control input-sm']
					) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.tax_decimals'), 'tax_decimals', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?php echo form_dropdown(
						'tax_decimals',
						[
							'0' => '0',
							'1' => '1',
							'2' => '2',
							'3' => '3',
							'4' => '4'
						],
						$config['tax_decimals'],
						['class' => 'form-control input-sm']
					) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.quantity_decimals'), 'quantity_decimals', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?php echo form_dropdown(
						'quantity_decimals',
						[
							'0' => '0',
							'1' => '1',
							'2' => '2',
							'3' => '3'
						],
						$config['quantity_decimals'],
						['class' => 'form-control input-sm']
					) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.cash_decimals'), 'cash_decimals', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?php echo form_dropdown(
						'cash_decimals',
						[
							'-1' => '-1',
							'0' => '0',
							'1' => '1',
							'2' => '2'
						],
						$config['cash_decimals'],
						['class' => 'form-control input-sm']
					) ?>
				</div>
				<div class='col-xs-1'>
					<label class="control-label">
						<span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-placement="right" title="<?php echo lang('Config.cash_decimals_tooltip') ?>"></span>
					</label>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.cash_rounding'), 'cash_rounding_code', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?php echo form_dropdown('cash_rounding_code', esc($rounding_options, 'attr'), $config['cash_rounding_code'], ['class' => 'form-control input-sm']) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.payment_options_order'), 'payment_options_order', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-4'>
					<?php echo form_dropdown(
						'payment_options_order',
						[
							'cashdebitcredit' => lang('Sales.cash') . ' / ' . lang('Sales.debit') . ' / ' . lang('Sales.credit'),
							'debitcreditcash' => lang('Sales.debit') . ' / ' . lang('Sales.credit') . ' / ' . lang('Sales.cash'),
							'debitcashcredit' => lang('Sales.debit') . ' / ' . lang('Sales.cash') . ' / ' . lang('Sales.credit'),
							'creditdebitcash' => lang('Sales.credit') . ' / ' . lang('Sales.debit') . ' / ' . lang('Sales.cash'),
							'creditcashdebit' => lang('Sales.credit') . ' / ' . lang('Sales.cash') . ' / ' . lang('Sales.debit')
						],
						esc($config['payment_options_order'], 'attr'),
						['class' => 'form-control input-sm']
					) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.country_codes'), 'country_codes', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-1'>
					<?php echo form_input('country_codes', esc($config['country_codes'], 'attr'), ['class' => 'form-control input-sm']) ?>
				</div>
				<div class="col-xs-1">
					<label class="control-label">
						<a href="http://wiki.openstreetmap.org/wiki/Nominatim/Country_Codes" target="_blank"><span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-placement="right" title="<?php echo lang('Config.country_codes_tooltip'); //TODO: May need to change the URL at the beginning to HTTPS?>"></span></a>
					</label>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.language'), 'language', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-4'>
					<?php echo form_dropdown(
							'language',
							get_languages(),
							current_language_code(TRUE) . ':' . current_language(TRUE),
							array('class' => 'form-control input-sm')
						)
					?>
				</div>
			</div>

			<div class="form-group form-group-sm">
			<?php echo form_label(lang('Config.timezone'), 'timezone', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-4'>
				<?php echo form_dropdown(
					'timezone',
					get_timezones(),
					$config['timezone'] ? esc($config['timezone'], 'attr') : date_default_timezone_get(), ['class' => 'form-control input-sm']) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
			<?php echo form_label(lang('Config.datetimeformat'), 'datetimeformat', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-sm-2'>
				<?php echo form_dropdown(
					'dateformat',
					get_dateformats(),
					esc($config['dateformat'], 'attr'),
					['class' => 'form-control input-sm']
				) ?>
				</div>
				<div class='col-sm-2'>
				<?php echo form_dropdown('timeformat',
					get_timeformats(),
					$config['timeformat'], ['class' => 'form-control input-sm']
				) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.date_or_time_format'), 'date_or_time_format', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?php echo form_checkbox ([
						'name' => 'date_or_time_format',
						'id' => 'date_or_time_format',
						'value' => 'date_or_time_format',
						'checked' => $config['date_or_time_format']
						]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label(lang('Config.financial_year'), 'financial_year', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?php echo form_dropdown(
						'financial_year',
						[
							'1' => lang('Config.financial_year_jan'),
							'2' => lang('Config.financial_year_feb'),
							'3' => lang('Config.financial_year_mar'),
							'4' => lang('Config.financial_year_apr'),
							'5' => lang('Config.financial_year_may'),
							'6' => lang('Config.financial_year_jun'),
							'7' => lang('Config.financial_year_jul'),
							'8' => lang('Config.financial_year_aug'),
							'9' => lang('Config.financial_year_sep'),
							'10' => lang('Config.financial_year_oct'),
							'11' => lang('Config.financial_year_nov'),
							'12' => lang('Config.financial_year_dec')
						],
						$config['financial_year'], ['class' => 'form-control input-sm']
					) ?>
				</div>
			</div>

			<?php echo form_submit ([
				'name' => 'submit_locale',
				'id' => 'submit_locale',
				'value' => lang('Common.submit'),
				'class' => 'btn btn-primary btn-sm pull-right']) ?>
		</fieldset>
	</div>
<?php echo form_close() ?>

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
		$.post("<?php echo esc(site_url($controller_name . '/ajax_check_number_locale'), 'url') //TODO: String Interpolation ?>",
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
					url: "<?php echo esc(site_url($controller_name . '/ajax_check_number_locale'), 'url') ?>",
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
				required: "<?php echo lang('Config.number_locale_required') ?>",
				number_locale: "<?php echo lang('Config.number_locale_invalid') ?>"
			}
		},

		errorLabelContainer: '#locale_error_message_box'
	}));
});
</script>

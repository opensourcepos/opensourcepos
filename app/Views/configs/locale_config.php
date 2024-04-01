<?php
/**
 * @var string $currency_code
 * @var array $rounding_options
 * @var string $controller_name
 * @var array $config
 */
?>

<?= form_open('config/saveLocale/', ['id' => 'locale_config_form', 'class' => 'form-horizontal']) ?>
	<div id="config_wrapper">
		<fieldset id="config_info">
			<div id="required_fields_message"><?= lang('Common.fields_required_message') ?></div>
			<ul id="locale_error_message_box" class="error_message_box"></ul>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.number_locale'), 'number_locale', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-1'>
					<?= form_input([
						'name' => 'number_locale',
						'id' => 'number_locale',
						'class' => 'form-control input-sm',
						'value' => $config['number_locale']
					]) ?>
					<?= form_hidden([
						'name' => 'save_number_locale',
						'value' => $config['number_locale']
					]) ?>
				</div>
				<div class="col-xs-2">
					<label class="control-label">
						<a href="https://github.com/opensourcepos/opensourcepos/wiki/Localisation-support" target="_blank">
							<span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-placement="right" title="<?= lang('Config.number_locale_tooltip') ?>"></span>
						</a>
						<span id="number_locale_example">
							&nbsp;&nbsp;<?= to_currency(1234567890.12300) ?>
						</span>
					</label>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.thousands_separator'), 'thousands_separator', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?= form_checkbox ([
						'name' => 'thousands_separator',
						'id' => 'thousands_separator',
						'value' => 'thousands_separator',
						'checked' => $config['thousands_separator'] == 1
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.currency_symbol'), 'currency_symbol', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-1'>
					<?= form_input ([
						'name' => 'currency_symbol',
						'id' => 'currency_symbol',
						'class' => 'form-control input-sm number_locale',
						'value' => $config['currency_symbol']
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.currency_code'), 'currency_code', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-1'>
					<?= form_input ([
						'name' => 'currency_code',
						'id' => 'currency_code',
						'class' => 'form-control input-sm number_locale',
						'value' => $currency_code
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.currency_decimals'), 'currency_decimals', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?= form_dropdown(
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
				<?= form_label(lang('Config.tax_decimals'), 'tax_decimals', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?= form_dropdown(
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
				<?= form_label(lang('Config.quantity_decimals'), 'quantity_decimals', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?= form_dropdown(
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
				<?= form_label(lang('Config.cash_decimals'), 'cash_decimals', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?= form_dropdown(
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
						<span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-placement="right" title="<?= lang('Config.cash_decimals_tooltip') ?>"></span>
					</label>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.cash_rounding'), 'cash_rounding_code', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?= form_dropdown(
						'cash_rounding_code',
						$rounding_options,
						$config['cash_rounding_code'],
						"class='form-control input-sm'"
					) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.payment_options_order'), 'payment_options_order', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-4'>
					<?= form_dropdown(
						'payment_options_order',
						[
							'cashdebitcredit' => lang('Sales.cash') . ' / ' . lang('Sales.debit') . ' / ' . lang('Sales.credit'),
							'debitcreditcash' => lang('Sales.debit') . ' / ' . lang('Sales.credit') . ' / ' . lang('Sales.cash'),
							'debitcashcredit' => lang('Sales.debit') . ' / ' . lang('Sales.cash') . ' / ' . lang('Sales.credit'),
							'creditdebitcash' => lang('Sales.credit') . ' / ' . lang('Sales.debit') . ' / ' . lang('Sales.cash'),
							'creditcashdebit' => lang('Sales.credit') . ' / ' . lang('Sales.cash') . ' / ' . lang('Sales.debit')
						],
						$config['payment_options_order'],
						"class='form-control input-sm'"
					) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.country_codes'), 'country_codes', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-1'>
					<?= form_input([
						'name' => 'country_codes',
						'class' => 'form-control input-sm',
						'value' => $config['country_codes']
					]) ?>
				</div>
				<div class="col-xs-1">
					<label class="control-label">
						<a href="http://wiki.openstreetmap.org/wiki/Nominatim/Country_Codes" target="_blank"><span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-placement="right" title="<?= lang('Config.country_codes_tooltip'); //TODO: May need to change the URL at the beginning to HTTPS?>"></span></a>
					</label>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.language'), 'language', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-4'>
					<?= form_dropdown(
							'language',
							get_languages(),
							current_language_code(true) . ':' . current_language(true),
							['class' => 'form-control input-sm']
						) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
			<?= form_label(lang('Config.timezone'), 'timezone', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-4'>
				<?= form_dropdown(
					'timezone',
					get_timezones(),
					$config['timezone'] ? $config['timezone'] : date_default_timezone_get(),
					['class' => 'form-control input-sm']
				) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
			<?= form_label(lang('Config.datetimeformat'), 'datetimeformat', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-sm-2'>
				<?= form_dropdown(
					'dateformat',
					get_dateformats(),
					$config['dateformat'],
					['class' => 'form-control input-sm']
				) ?>
				</div>
				<div class='col-sm-2'>
				<?= form_dropdown(
					'timeformat',
					get_timeformats(),
					$config['timeformat'],
					['class' => 'form-control input-sm']
				) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.date_or_time_format'), 'date_or_time_format', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?= form_checkbox ([
						'name' => 'date_or_time_format',
						'id' => 'date_or_time_format',
						'value' => 'date_or_time_format',
						'checked' => $config['date_or_time_format'] == 1
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.financial_year'), 'financial_year', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?= form_dropdown(
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
						$config['financial_year'],
						['class' => 'form-control input-sm']
					) ?>
				</div>
			</div>

			<?= form_submit ([
				'name' => 'submit_locale',
				'id' => 'submit_locale',
				'value' => lang('Common.submit'),
				'class' => 'btn btn-primary btn-sm pull-right']) ?>
		</fieldset>
	</div>
<?= form_close() ?>

<script type="application/javascript">
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
		$.post("<?= "$controller_name /checkNumberLocale" ?>",
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
					url: "<?= "$controller_name/checkNumberLocale" ?>",
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
				required: "<?= lang('Config.number_locale_required') ?>",
				number_locale: "<?= lang('Config.number_locale_invalid') ?>"
			}
		},

		errorLabelContainer: '#locale_error_message_box'
	}));
});
</script>

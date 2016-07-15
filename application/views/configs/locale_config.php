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
					</div>
					<div class="col-xs-2">
						<label class="control-label">
							<a href="https://github.com/jekkos/opensourcepos/wiki/Localisation-support" target="_blank">
								<span class="glyphicon glyphicon-info-sign" data-toggle="tootltip" data-placement="right" title="<?php echo $this->lang->line('config_number_locale_tooltip'); ?>"></span>
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
				<?php echo form_label($this->lang->line('config_tax_decimals'), 'language', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_dropdown('tax_decimals', array(
						'0' => '0',
						'1' => '1',
						'2' => '2',
						'3' => '3'
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
				<?php echo form_label($this->lang->line('config_payment_options_order'), 'payment_options_order', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-4'>
					<?php echo form_dropdown('payment_options_order', array(
						'cashdebitcredit' => $this->lang->line('sales_cash') . ' / ' . $this->lang->line('sales_debit') . ' / ' . $this->lang->line('sales_credit'),
						'debitcreditcash' => $this->lang->line('sales_debit') . ' / ' . $this->lang->line('sales_credit') . ' / ' . $this->lang->line('sales_cash'),
						'debitcashcredit' => $this->lang->line('sales_debit') . ' / ' . $this->lang->line('sales_cash') . ' / ' . $this->lang->line('sales_credit')
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
				<div class="checkbox col-xs-1">
					<a href="http://wiki.openstreetmap.org/wiki/Nominatim/Country_Codes" target="_blank"><span class="glyphicon glyphicon-info-sign" data-toggle="tootltip" data-placement="right" title="<?php echo $this->lang->line('config_country_codes_tooltip'); ?>"></span></a>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_language'), 'language', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-4'>
					<?php echo form_dropdown('language', array(
						'en' => 'English',
						'es' => 'Spanish',
						'nl-BE' => 'Dutch (Belgium)',
						'de' => 'German (Germany)',
						'de-CH' => 'German (Swiss)',
						'fr' => 'French',
						'zh' => 'Chinese',
						'id' => 'Indonesian',
						'th' => 'Thai',
						'tr' => 'Turkish',
						'ru' => 'Russian',
						'hu-HU' => 'Hungarian',
						'pt-BR' => 'Portuguese (Brazil)',
						'hr-HR' => 'Croatian (Croatia)'
					),
					$this->config->item('language'), array('class' => 'form-control input-sm'));
					?>
				</div>
			</div>

			<div class="form-group form-group-sm">	
			<?php echo form_label($this->lang->line('config_timezone'), 'timezone', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-4'>
				<?php echo form_dropdown('timezone', 
				 array(
					'Pacific/Midway' => '(GMT-11:00) Midway Island, Samoa',
					'America/Adak' => '(GMT-10:00) Hawaii-Aleutian',
					'Etc/GMT+10' => '(GMT-10:00) Hawaii',
					'Pacific/Marquesas' => '(GMT-09:30) Marquesas Islands',
					'Pacific/Gambier' => '(GMT-09:00) Gambier Islands',
					'America/Anchorage' => '(GMT-09:00) Alaska',
					'America/Ensenada' => '(GMT-08:00) Tijuana, Baja California',
					'Etc/GMT+8' => '(GMT-08:00) Pitcairn Islands',
					'America/Los_Angeles' => '(GMT-08:00) Pacific Time (US & Canada)',
					'America/Denver' => '(GMT-07:00) Mountain Time (US & Canada)',
					'America/Chihuahua' => '(GMT-07:00) Chihuahua, La Paz, Mazatlan',
					'America/Dawson_Creek' => '(GMT-07:00) Arizona',
					'America/Belize' => '(GMT-06:00) Saskatchewan, Central America',
					'America/Cancun' => '(GMT-06:00) Guadalajara, Mexico City, Monterrey',
					'Chile/EasterIsland' => '(GMT-06:00) Easter Island',
					'America/Chicago' => '(GMT-06:00) Central Time (US & Canada)',
					'America/New_York' => '(GMT-05:00) Eastern Time (US & Canada)',
					'America/Havana' => '(GMT-05:00) Cuba',
					'America/Bogota' => '(GMT-05:00) Bogota, Lima, Quito, Rio Branco',
					'America/Caracas' => '(GMT-04:30) Caracas',
					'America/Santiago' => '(GMT-04:00) Santiago',
					'America/La_Paz' => '(GMT-04:00) La Paz',
					'Atlantic/Stanley' => '(GMT-04:00) Faukland Islands',
					'America/Campo_Grande' => '(GMT-04:00) Brazil',
					'America/Goose_Bay' => '(GMT-04:00) Atlantic Time (Goose Bay)',
					'America/Glace_Bay' => '(GMT-04:00) Atlantic Time (Canada)',
					'America/St_Johns' => '(GMT-03:30) Newfoundland',
					'America/Araguaina' => '(GMT-03:00) UTC-3',
					'America/Montevideo' => '(GMT-03:00) Montevideo',
					'America/Miquelon' => '(GMT-03:00) Miquelon, St. Pierre',
					'America/Godthab' => '(GMT-03:00) Greenland',
					'America/Argentina/Buenos_Aires' => '(GMT-03:00) Buenos Aires',
					'America/Sao_Paulo' => '(GMT-03:00) Brasilia',
					'America/Noronha' => '(GMT-02:00) Mid-Atlantic',
					'Atlantic/Cape_Verde' => '(GMT-01:00) Cape Verde Is.',
					'Atlantic/Azores' => '(GMT-01:00) Azores',
					'Europe/Belfast' => '(GMT) Greenwich Mean Time : Belfast',
					'Europe/Dublin' => '(GMT) Greenwich Mean Time : Dublin',
					'Europe/Lisbon' => '(GMT) Greenwich Mean Time : Lisbon',
					'Europe/London' => '(GMT) Greenwich Mean Time : London',
					'Africa/Abidjan' => '(GMT) Monrovia, Reykjavik',
					'Europe/Amsterdam' => '(GMT+01:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna',
					'Europe/Belgrade' => '(GMT+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague',
					'Europe/Brussels' => '(GMT+01:00) Brussels, Copenhagen, Madrid, Paris',
					'Africa/Algiers' => '(GMT+01:00) West Central Africa',
					'Africa/Windhoek' => '(GMT+01:00) Windhoek',
					'Asia/Beirut' => '(GMT+02:00) Beirut',
					'Africa/Cairo' => '(GMT+02:00) Cairo',
					'Asia/Gaza' => '(GMT+02:00) Gaza',
					'Africa/Blantyre' => '(GMT+02:00) Harare, Pretoria',
					'Asia/Jerusalem' => '(GMT+02:00) Jerusalem',
					'Europe/Minsk' => '(GMT+02:00) Minsk',
					'Asia/Damascus' => '(GMT+02:00) Syria',
					'Europe/Moscow' => '(GMT+03:00) Moscow, St. Petersburg, Volgograd',
					'Africa/Addis_Ababa' => '(GMT+03:00) Nairobi',
					'Asia/Tehran' => '(GMT+03:30) Tehran',
					'Asia/Dubai' => '(GMT+04:00) Abu Dhabi, Muscat',
					'Asia/Yerevan' => '(GMT+04:00) Yerevan',
					'Asia/Kabul' => '(GMT+04:30) Kabul',
					'Asia/Baku' => '(GMT+05:00) Baku',
					'Asia/Yekaterinburg' => '(GMT+05:00) Ekaterinburg',
					'Asia/Tashkent' => '(GMT+05:00) Tashkent',
					'Asia/Kolkata' => '(GMT+05:30) Chennai, Kolkata, Mumbai, New Delhi',
					'Asia/Katmandu' => '(GMT+05:45) Kathmandu',
					'Asia/Dhaka' => '(GMT+06:00) Astana, Dhaka',
					'Asia/Novosibirsk' => '(GMT+06:00) Novosibirsk',
					'Asia/Rangoon' => '(GMT+06:30) Yangon (Rangoon)',
					'Asia/Bangkok' => '(GMT+07:00) Bangkok, Hanoi, Jakarta',
					'Asia/Krasnoyarsk' => '(GMT+07:00) Krasnoyarsk',
					'Asia/Hong_Kong' => '(GMT+08:00) Beijing, Chongqing, Hong Kong, Urumqi',
					'Asia/Irkutsk' => '(GMT+08:00) Irkutsk, Ulaan Bataar',
					'Australia/Perth' => '(GMT+08:00) Perth',
					'Australia/Eucla' => '(GMT+08:45) Eucla',
					'Asia/Tokyo' => '(GMT+09:00) Osaka, Sapporo, Tokyo',
					'Asia/Seoul' => '(GMT+09:00) Seoul',
					'Asia/Yakutsk' => '(GMT+09:00) Yakutsk',
					'Australia/Adelaide' => '(GMT+09:30) Adelaide',
					'Australia/Darwin' => '(GMT+09:30) Darwin',
					'Australia/Brisbane' => '(GMT+10:00) Brisbane',
					'Australia/Hobart' => '(GMT+10:00) Hobart',
					'Asia/Vladivostok' => '(GMT+10:00) Vladivostok',
					'Australia/Lord_Howe' => '(GMT+10:30) Lord Howe Island',
					'Etc/GMT-11' => '(GMT+11:00) Solomon Is., New Caledonia',
					'Asia/Magadan' => '(GMT+11:00) Magadan',
					'Pacific/Norfolk' => '(GMT+11:30) Norfolk Island',
					'Asia/Anadyr' => '(GMT+12:00) Anadyr, Kamchatka',
					'Pacific/Auckland' => '(GMT+12:00) Auckland, Wellington',
					'Etc/GMT-12' => '(GMT+12:00) Fiji, Kamchatka, Marshall Is.',
					'Pacific/Chatham' => '(GMT+12:45) Chatham Islands',
					'Pacific/Tongatapu' => '(GMT+13:00) Nuku\'alofa',
					'Pacific/Kiritimati' => '(GMT+14:00) Kiritimati'
					),
					$this->config->item('timezone') ? $this->config->item('timezone') : date_default_timezone_get(), array('class' => 'form-control input-sm'));
					?>
				</div>
			</div>

			<div class="form-group form-group-sm">	
			<?php echo form_label($this->lang->line('config_datetimeformat'), 'datetimeformat', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-sm-2'>
				<?php echo form_dropdown('dateformat', array(
					'd/m/Y' => 'dd/mm/yyyy',
					'd.m.Y' => 'dd.mm.yyyy',
					'm/d/Y' => 'mm/dd/yyyy',
					'Y/m/d' => 'yyyy/mm/dd',
					'd/m/y' => 'dd/mm/yy',
					'm/d/y' => 'mm/dd/yy',
					'y/m/d' => 'yy/mm/dd'
					),
					$this->config->item('dateformat'), array('class' => 'form-control input-sm'));
					?>
				</div>
				<div class='col-sm-2'>
				<?php echo form_dropdown('timeformat', array(
					'H:i:s' => 'hh:mm:ss (24h)',
					'h:i:s a' => 'hh:mm:ss am/pm',
					'h:i:s A' => 'hh:mm:ss AM/PM'
					),
					$this->config->item('timeformat'), array('class' => 'form-control input-sm'));
					?>
				</div>
			</div>

			<?php echo form_submit(array(
				'name' => 'submit_form',
				'id' => 'submit_form',
				'value'=>$this->lang->line('common_submit'),
				'class' => 'btn btn-primary btn-sm pull-right')); ?>
		</fieldset>
	</div>
<?php echo form_close(); ?>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{
	$("span").tooltip();

	var number_locale_params = {
		url: "<?php echo site_url($controller_name . '/check_number_locale')?>",
		type: "POST"
	};

	$("#currency_symbol, #thousands_separator").change(function() {
		var field = $(this).attr('id');
		var value = $(this).is(":checkbox") ? $(this).is(":checked") : $(this).val();
		var data =
		{
			number_locale: $("#number_locale").val()
		};
		data[field] = value;
		$.post($.extend(number_locale_params, {
			data: $.extend(csrf_form_base(), data),
			success: function(response) {
				$("#number_locale_example").text(response.number_locale_example);
			}
		}));
	});

	$('#locale_config_form').validate($.extend(form_support.handler, {

		rules:
		{
			number_locale:
			{
				required: true,
				remote: $.extend(number_locale_params, {
					data: $.extend(csrf_form_base(), {
						"number_locale" : function() {
							return $("#number_locale").val();
						},
						"thousands_separator": function() {
							return $("#thousands_separator").is(":checked");
						}
					}),
					dataFilter: function(data, dataType) {
						setup_csrf_token();
						var response = JSON.parse(data);
						$("#number_locale_example").text(response.number_locale_example);
						$("#currency_symbol").val(response.currency_symbol);
						$("#thousands_separator").prop('checked', response.thousands_separator);
						return response.success;
					}
				})
			}
		},

		messages:
		{
			number_locale: {
				required: '<?php echo $this->lang->line('config_number_locale_required') ?>',
				number_locale: '<?php echo $this->lang->line('config_number_locale_invalid') ?>'
			}
		},
		errorLabelContainer: "#locale_error_message_box"
	}));
});
</script>

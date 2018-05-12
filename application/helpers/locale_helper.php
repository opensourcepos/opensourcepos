<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Currency locale helper
 */

function current_language_code($load_system_language = FALSE)
{
	$employee = get_instance()->Employee;

	// Returns the language code of the employee if set or system language code if not
	if($employee->is_logged_in() && $load_system_language != TRUE)
	{
		$employee_language_code = $employee->get_logged_in_employee_info()->language_code;
		if($employee_language_code != NULL && $employee_language_code != '')
		{
			return $employee_language_code;
		}
	}

	return get_instance()->config->item('language_code');
}

function current_language($load_system_language = FALSE)
{
	$employee = get_instance()->Employee;

	// Returns the language of the employee if set or system language if not
	if($employee->is_logged_in() && $load_system_language != TRUE)
	{
		$employee_language = $employee->get_logged_in_employee_info()->language;
		if($employee_language != NULL && $employee_language != '')
		{
			return $employee_language;
		}
	}

	return get_instance()->config->item('language');
}

function get_languages()
{
	return array(
		'ar-EG:arabic' => 'Arabic (Egypt)',
		'az-AZ:azerbaijani' => 'Azerbaijani (Azerbaijan)',
		'bg:bulgarian' => 'Bulgarian',
		'de:german' => 'German (Germany)',
		'de-CH:german' => 'German (Swiss)',
		'en-GB:english' => 'English (Great Britain)',
		'en-US:english' => 'English (United States)',
		'es:spanish' => 'Spanish',
		'fr:french' => 'French',
		'hr-HR:croatian' => 'Croatian (Croatia)',
		'hu-HU:hungarian' => 'Hungarian (Hungary)',
		'id:indonesian' => 'Indonesian',
		'it:italian' => 'Italian',
		'km:khmer' => 'Central Khmer (Cambodia)',
		'lo:lao' => 'Lao (Laos)',
		'nl-BE:dutch' => 'Dutch (Belgium)',
		'pt-BR:portuguese-brazilian' => 'Portuguese (Brazil)',
		'ru:russian' => 'Russian',
		'sv:swedish' => 'Swedish',
		'th:thai' => 'Thai',
		'tr:turkish' => 'Turkish',
		'vi:vietnamese' => 'Vietnamese',
		'zh:simplified-chinese' => 'Chinese'
	);
}

function load_language($load_system_language = FALSE, array $lang_array)
{
	$lang = get_instance()->lang;

	if($load_system_language = TRUE)
	{
		foreach($lang_array as $language_file)
		{
			$lang->load($language_file, current_language_code(TRUE));
		}
	}
	else
	{
		foreach($lang_array as $language_file)
		{
			$lang->load($language_file, current_language_code());
		}
	}
}

function get_timezones()
{
	return array(
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
		'Atlantic/Stanley' => '(GMT-04:00) Falkland Islands',
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
		'Asia/Baku' => '(GMT+04:00) Baku',
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
	);
}

function get_dateformats()
{
	return array(
		'd/m/Y' => 'dd/mm/yyyy',
		'd.m.Y' => 'dd.mm.yyyy',
		'm/d/Y' => 'mm/dd/yyyy',
		'Y/m/d' => 'yyyy/mm/dd',
		'd/m/y' => 'dd/mm/yy',
		'm/d/y' => 'mm/dd/yy',
		'y/m/d' => 'yy/mm/dd'
	);
}

function get_timeformats()
{
	return array(
		'H:i:s' => 'hh:mm:ss (24h)',
		'h:i:s a' => 'hh:mm:ss am/pm',
		'h:i:s A' => 'hh:mm:ss AM/PM'
	);
}

/*
Gets the payment options
*/
function get_payment_options()
{
	$config = get_instance()->config;
	$lang = get_instance()->lang;

	$payments = array();

	if($config->item('payment_options_order') == 'debitcreditcash')
	{
		$payments[$lang->line('sales_debit')] = $lang->line('sales_debit');
		$payments[$lang->line('sales_credit')] = $lang->line('sales_credit');
		$payments[$lang->line('sales_cash')] = $lang->line('sales_cash');
	}
	elseif($config->item('payment_options_order') == 'debitcashcredit')
	{
		$payments[$lang->line('sales_debit')] = $lang->line('sales_debit');
		$payments[$lang->line('sales_cash')] = $lang->line('sales_cash');
		$payments[$lang->line('sales_credit')] = $lang->line('sales_credit');
	}
	elseif($config->item('payment_options_order') == 'creditdebitcash')
	{
		$payments[$lang->line('sales_credit')] = $lang->line('sales_credit');
		$payments[$lang->line('sales_debit')] = $lang->line('sales_debit');
		$payments[$lang->line('sales_cash')] = $lang->line('sales_cash');
	}
	elseif($config->item('payment_options_order') == 'creditcashdebit')
	{
		$payments[$lang->line('sales_credit')] = $lang->line('sales_credit');
		$payments[$lang->line('sales_cash')] = $lang->line('sales_cash');
		$payments[$lang->line('sales_debit')] = $lang->line('sales_debit');
	}
	else // default: if($config->item('payment_options_order') == 'cashdebitcredit')
	{
		$payments[$lang->line('sales_cash')] = $lang->line('sales_cash');
		$payments[$lang->line('sales_debit')] = $lang->line('sales_debit');
		$payments[$lang->line('sales_credit')] = $lang->line('sales_credit');
	}

	$payments[$lang->line('sales_due')] = $lang->line('sales_due');
	$payments[$lang->line('sales_check')] = $lang->line('sales_check');

	return $payments;
}

function currency_side()
{
	$config = get_instance()->config;

	$fmt = new \NumberFormatter($config->item('number_locale'), \NumberFormatter::CURRENCY);
	$fmt->setSymbol(\NumberFormatter::CURRENCY_SYMBOL, $config->item('currency_symbol'));

	return !preg_match('/^¤/', $fmt->getPattern());
}

function quantity_decimals()
{
	$config = get_instance()->config;

	return $config->item('quantity_decimals') ? $config->item('quantity_decimals') : 0;
}

function totals_decimals()
{
	$config = get_instance()->config;

	return $config->item('currency_decimals') ? $config->item('currency_decimals') : 0;
}

function cash_decimals()
{
	$config = get_instance()->config;

	return $config->item('cash_decimals') ? $config->item('cash_decimals') : 0;
}

function tax_decimals()
{
	$config = get_instance()->config;

	return $config->item('tax_decimals') ? $config->item('tax_decimals') : 0;
}

function to_currency($number)
{
	return to_decimals($number, 'currency_decimals', \NumberFormatter::CURRENCY);
}

function to_currency_no_money($number)
{
	return to_decimals($number, 'currency_decimals');
}

function to_currency_tax($number)
{
	$config = get_instance()->config;

	if($config->item('customer_sales_tax_support') == '1')
	{
		return to_decimals($number, 'currency_decimals', \NumberFormatter::CURRENCY);
	}
	else
	{
		return to_decimals($number, 'tax_decimals', \NumberFormatter::CURRENCY);
	}
}

function to_tax_decimals($number)
{
	// taxes that are NULL, '' or 0 don't need to be displayed
	// NOTE: do not remove this line otherwise the items edit form will show a tax with 0 and it will save it
	if(empty($number))
	{
		return $number;
	}

	return to_decimals($number, 'tax_decimals');
}

function to_quantity_decimals($number)
{
	return to_decimals($number, 'quantity_decimals');
}

function to_decimals($number, $decimals, $type=\NumberFormatter::DECIMAL)
{
	// ignore empty strings and return
	// NOTE: do not change it to empty otherwise tables will show a 0 with no decimal nor currency symbol
	if(!isset($number))
	{
		return $number;
	}

	$config = get_instance()->config;
	$fmt = new \NumberFormatter($config->item('number_locale'), $type);
	$fmt->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $config->item($decimals));
	$fmt->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $config->item($decimals));
	if(empty($config->item('thousands_separator')))
	{
		$fmt->setAttribute(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL, '');
	}
	$fmt->setSymbol(\NumberFormatter::CURRENCY_SYMBOL, $config->item('currency_symbol'));

	return $fmt->format($number);
}

function parse_decimals($number)
{
	// ignore empty strings and return
	if(empty($number))
	{
		return $number;
	}

	$config = get_instance()->config;
	$fmt = new \NumberFormatter($config->item('number_locale'), \NumberFormatter::DECIMAL);

	$fmt->setAttribute(\NumberFormatter::FRACTION_DIGITS, $config->item('currency_decimals'));

	if(empty($config->item('thousands_separator')))
	{
		$fmt->setAttribute(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL, '');
	}

	try
	{
		return $fmt->parse($number);
	}
	catch(Exception $e)
	{
		return FALSE;
	}
}

/*
 * Time locale conversion utility
 */

function dateformat_momentjs($php_format)
{
	$SYMBOLS_MATCHING = array(
		'd' => 'DD',
		'D' => 'ddd',
		'j' => 'D',
		'l' => 'dddd',
		'N' => 'E',
		'S' => 'o',
		'w' => 'e',
		'z' => 'DDD',
		'W' => 'W',
		'F' => 'MMMM',
		'm' => 'MM',
		'M' => 'MMM',
		'n' => 'M',
		't' => '', // no equivalent
		'L' => '', // no equivalent
		'o' => 'YYYY',
		'Y' => 'YYYY',
		'y' => 'YY',
		'a' => 'a',
		'A' => 'A',
		'B' => '', // no equivalent
		'g' => 'h',
		'G' => 'H',
		'h' => 'hh',
		'H' => 'HH',
		'i' => 'mm',
		's' => 'ss',
		'u' => 'SSS',
		'e' => 'zz', // deprecated since version $1.6.0 of moment.js
		'I' => '', // no equivalent
		'O' => '', // no equivalent
		'P' => '', // no equivalent
		'T' => '', // no equivalent
		'Z' => '', // no equivalent
		'c' => '', // no equivalent
		'r' => '', // no equivalent
		'U' => 'X'
	);

	return strtr($php_format, $SYMBOLS_MATCHING);
}

function dateformat_bootstrap($php_format)
{
	$SYMBOLS_MATCHING = array(
		// Day
		'd' => 'dd',
		'D' => 'd',
		'j' => 'd',
		'l' => 'dd',
		'N' => '',
		'S' => '',
		'w' => '',
		'z' => '',
		// Week
		'W' => '',
		// Month
		'F' => 'MM',
		'm' => 'mm',
		'M' => 'M',
		'n' => 'm',
		't' => '',
		// Year
		'L' => '',
		'o' => '',
		'Y' => 'yyyy',
		'y' => 'yy',
		// Time
		'a' => 'p',
		'A' => 'P',
		'B' => '',
		'g' => 'H',
		'G' => 'h',
		'h' => 'HH',
		'H' => 'hh',
		'i' => 'ii',
		's' => 'ss',
		'u' => ''
	);

	return strtr($php_format, $SYMBOLS_MATCHING);
}

?>

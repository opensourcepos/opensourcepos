<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Currency locale helper
 */

function current_language_code($load_system_language = FALSE)
{
	// Returns the language code of the employee if set or system language code if not
	if(get_instance()->Employee->is_logged_in() && $load_system_language != TRUE)
	{
		$employee_language_code = get_instance()->Employee->get_logged_in_employee_info()->language_code;
		if($employee_language_code != NULL && $employee_language_code != '')
		{
			return $employee_language_code;
		}
	}
	return get_instance()->config->item('language_code');
}

function current_language($load_system_language = FALSE)
{
	// Returns the language of the employee if set or system language if not
	if(get_instance()->Employee->is_logged_in() && $load_system_language != TRUE)
	{
		$employee_language = get_instance()->Employee->get_logged_in_employee_info()->language;
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
		'en-US:english' => 'English (United States)',
		'en-GB:english' => 'English (Great Britain)',
		'es:spanish' => 'Spanish',
		'nl-BE:dutch' => 'Dutch (Belgium)',
		'de:german' => 'German (Germany)',
		'de-CH:german' => 'German (Swiss)',
		'fr:french' => 'French',
		'zh:simplified-chinese' => 'Chinese',
		'id:indonesian' => 'Indonesian',
		'th:thai' => 'Thai',
		'tr:turkish' => 'Turkish',
		'ru:russian' => 'Russian',
		'hu-HU:hungarian' => 'Hungarian',
		'pt-BR:portuguese-brazilian' => 'Portuguese (Brazil)',
		'hr-HR' => 'Croatian (Croatia)',
		'ar-EG:arabic' => 'Arabic (Egypt)',
		'az-AZ:azerbaijani' => 'Azerbaijani (Azerbaijan)'
	);
}

function load_language($load_system_language = FALSE, array $lang_array)
{
	if($load_system_language = TRUE)
	{
		foreach($lang_array as $language_file) 
		{
			get_instance()->lang->load($language_file,current_language_code(TRUE));
		}
	}
	else
	{
		foreach($lang_array as $language_file)
		{
			get_instance()->lang->load($language_file,current_language_code());
		}
	}
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
    $fmt = new \NumberFormatter( $config->item('number_locale'), \NumberFormatter::DECIMAL );
    if (empty($config->item('thousands_separator')))
    {
        $fmt->setAttribute(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL, '');
    }

    return $fmt->parse($number);
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

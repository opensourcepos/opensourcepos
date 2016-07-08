<?php

/*
 * Currency locale
 */

function currency_side()
{
    $CI =& get_instance();
    $fmt = new \NumberFormatter($CI->config->item('number_locale'), \NumberFormatter::CURRENCY);
    $fmt->setSymbol(\NumberFormatter::CURRENCY_SYMBOL, $CI->config->item('currency_symbol'));
    return !preg_match('/^¤/', $fmt->getPattern());
}

function quantity_decimals()
{
    $CI =& get_instance();
    return $CI->config->item('quantity_decimals') ? $CI->config->item('quantity_decimals') : 0;
}

function totals_decimals()
{
	$CI =& get_instance();
	return $CI->config->item('currency_decimals') ? $CI->config->item('currency_decimals') : 0;
}

function to_currency($number, $escape = FALSE)
{
    return to_decimals($number, 'currency_decimals', \NumberFormatter::CURRENCY);
}

function to_currency_no_money($number)
{
    return to_decimals($number, 'currency_decimals');
}

function to_tax_decimals($number)
{
    if (empty($number))
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
    $CI =& get_instance();
    $fmt = new \NumberFormatter($CI->config->item('number_locale'), $type);
    $fmt->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $CI->config->item($decimals));
    $fmt->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $CI->config->item($decimals));
    $fmt->setSymbol(\NumberFormatter::CURRENCY_SYMBOL, $CI->config->item('currency_symbol'));
    return $fmt->format($number);
}

function parse_decimals($number)
{
    // ignore empty strings as they are just for empty input
    if (empty($number))
    {
        return $number;
    }
    $CI =& get_instance();
    $fmt = new \NumberFormatter( $CI->config->item('number_locale'), \NumberFormatter::DECIMAL );
    $fmt->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $CI->config->item('quantity_decimals'));
    $fmt->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $CI->config->item('quantity_decimals'));
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

<?php

/*
 * Currency locale
 */

function to_currency($number, $escape=FALSE)
{
	$CI =& get_instance();

	$currency_symbol = $CI->config->item('currency_symbol') ? $CI->config->item('currency_symbol') : '$';
	$currency_symbol = $currency_symbol == '$' && $escape ? '\$' : $currency_symbol; 
	$thousands_separator = $CI->config->item('thousands_separator') ? $CI->config->item('thousands_separator') : '';
	$decimal_point = $CI->config->item('decimal_point') ? $CI->config->item('decimal_point') : '.';
	$decimals = $CI->config->item('currency_decimals') ? $CI->config->item('currency_decimals') : 0;

	if($number >= 0)
	{
		if(!$CI->config->item('currency_side'))
			return $currency_symbol.number_format($number, $decimals, $decimal_point, $thousands_separator);
		else
			return number_format($number, $decimals, $decimal_point, $thousands_separator).$currency_symbol;
	}
    else
    {
    	if(!$CI->config->item('currency_side'))
    		return '-'.$currency_symbol.number_format(abs($number), $decimals, $decimal_point, $thousands_separator);
    	else
    		return '-'.number_format(abs($number), $decimals, $decimal_point, $thousands_separator).$currency_symbol;
    }
}

function to_currency_no_money($number)
{
	// ignore empty strings as they are just for empty input
	if( empty($number) )
	{
		return $number;
	}

	$CI =& get_instance();

	$decimals = $CI->config->item('currency_decimals') ? $CI->config->item('currency_decimals') : 0;

	return number_format($number, $decimals, '.', '');
}

function totals_decimals()
{
	$CI =& get_instance();
	
	$decimals = $CI->config->item('currency_decimals') ? $CI->config->item('currency_decimals') : 0;

	return $decimals;
}


/*
 * Tax locale
 */

function to_tax_decimals($number)
{
	// ignore empty strings as they are just for empty input
	if( empty($number) )
	{
		return $number;
	}
	
	$CI =& get_instance();

	$decimal_point = $CI->config->item('decimal_point') ? $CI->config->item('decimal_point') : '.';
	$decimals = $CI->config->item('tax_decimals') ? $CI->config->item('tax_decimals') : 0;

	return number_format($number, $decimals, $decimal_point, '');
}


/*
 * Quantity decimals
 */

function to_quantity_decimals($number)
{
	$CI =& get_instance();

	$decimal_point = $CI->config->item('decimal_point') ? $CI->config->item('decimal_point') : '.';
	$decimals = $CI->config->item('quantity_decimals') ? $CI->config->item('quantity_decimals') : 0;

	return number_format($number, $decimals, $decimal_point, '');
}

function quantity_decimals()
{
	$CI =& get_instance();

	return $CI->config->item('quantity_decimals') ? $CI->config->item('quantity_decimals') : 0;
}


/*
 * Matches each symbol of PHP date format standard
 * with jQuery equivalent codeword
 * @author Tristan Jahier
 */
function dateformat_jquery($php_format)
{
    $SYMBOLS_MATCHING = array(
        // Day
        'd' => 'dd',
        'D' => 'D',
        'j' => 'd',
        'l' => 'DD',
        'N' => '',
        'S' => '',
        'w' => '',
        'z' => 'o',
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
        'Y' => 'yy',
        'y' => 'y',
        // Time
        'a' => 'tt',
        'A' => 'TT',
        'B' => '',
        'g' => 'h',
        'G' => 'H',
        'h' => 'hh',
        'H' => 'HH',
        'i' => 'mm',
        's' => 'ss',
        'u' => ''
    );

    $jqueryui_format = "";
    $escaping = false;
    for($i = 0; $i < strlen($php_format); $i++)
    {
        $char = $php_format[$i];
        if($char === '\\') // PHP date format escaping character
        {
            $i++;
            if($escaping) $jqueryui_format .= $php_format[$i];
            else $jqueryui_format .= '\'' . $php_format[$i];
            $escaping = true;
        }
        else
        {
            if($escaping) { $jqueryui_format .= "'"; $escaping = false; }
            if(isset($SYMBOLS_MATCHING[$char]))
                $jqueryui_format .= $SYMBOLS_MATCHING[$char];
            else
                $jqueryui_format .= $char;
        }
    }
    return $jqueryui_format;
}

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

    $bootstrap_format = "";
    $escaping = false;
    for($i = 0; $i < strlen($php_format); $i++)
    {
        $char = $php_format[$i];
        if($char === '\\') // PHP date format escaping character
        {
            $i++;
            if($escaping) $bootstrap_format .= $php_format[$i];
            else $bootstrap_format .= '\'' . $php_format[$i];
            $escaping = true;
        }
        else
        {
            if($escaping) { $bootstrap_format .= "'"; $escaping = false; }
            if(isset($SYMBOLS_MATCHING[$char]))
                $bootstrap_format .= $SYMBOLS_MATCHING[$char];
            else
                $bootstrap_format .= $char;
        }
	}
	
    return $bootstrap_format;
}

?>

<?php

function to_currency($number, $escape=FALSE)
{
	$CI =& get_instance();

	$currency_symbol = $CI->config->item('currency_symbol') ? $CI->config->item('currency_symbol') : '$';
	$currency_symbol = $currency_symbol == '$' && $escape ? '\$' : $currency_symbol; 
	$thousands_separator = $CI->config->item('thousands_separator') ? $CI->config->item('thousands_separator') : '';
	$decimal_point = $CI->config->item('decimal_point') ? $CI->config->item('decimal_point') : '.';
	$decimals = $CI->config->item('currency_decimals') ? $CI->config->item('currency_decimals') : 2;

	if($number >= 0)
	{
		if($CI->config->item('currency_side') !== 'currency_side')
			return $currency_symbol.number_format($number, $decimals, $decimal_point, $thousands_separator);
		else
			return number_format($number, $decimals, $decimal_point, $thousands_separator).$currency_symbol;
	}
    else
    {
    	if($CI->config->item('currency_side') !== 'currency_side')
    		return '-'.$currency_symbol.number_format(abs($number), $decimals, $decimal_point, $thousands_separator);
    	else
    		return '-'.number_format(abs($number), $decimals, $decimal_point, $thousands_separator).$currency_symbol;
    }
}

function to_currency_no_money($number)
{
	$CI =& get_instance();

	$thousands_separator = $CI->config->item('thousands_separator') ? $CI->config->item('thousands_separator') : '';
	$decimal_point = $CI->config->item('decimal_point') ? $CI->config->item('decimal_point') : '.';
	$decimals = $CI->config->item('currency_decimals') ? $CI->config->item('currency_decimals') : 2;

	return number_format($number, $decimals, $decimal_point, $thousands_separator);
}

?>

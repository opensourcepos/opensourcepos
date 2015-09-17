<?php
function to_currency($number,$escape=FALSE)
{
	$CI =& get_instance();
	$currency_symbol = $CI->config->item('currency_symbol') ? $CI->config->item('currency_symbol') : '$';
	$currency_symbol = $currency_symbol == '$' && $escape ? '\$' : $currency_symbol; 
	$thousands_separator = $CI->config->item('thousands_separator') ? $CI->config->item('thousands_separator') : '';
	$decimal_point = $CI->config->item('decimal_point') ? $CI->config->item('decimal_point') : '.';
	if($number >= 0)
	{
		if($CI->config->item('currency_side') !== 'currency_side')
			return $currency_symbol.number_format($number, 2, $decimal_point, $thousands_separator);
		else
			return number_format($number, 2, $decimal_point, $thousands_separator).$currency_symbol;
	}
    else
    {
    	if($CI->config->item('currency_side') !== 'currency_side')
    		return '-'.$currency_symbol.number_format(abs($number), 2, $decimal_point, $thousands_separator);
    	else
    		return '-'.number_format(abs($number), 2, $decimal_point, $thousands_separator).$currency_symbol;
    }
}

function to_currency_no_money($number)
{
	return number_format($number, 2, '.', '');
}
?>

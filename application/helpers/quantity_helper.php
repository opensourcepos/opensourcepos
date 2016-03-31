<?php

function to_quantity($number)
{
	$CI =& get_instance();
	
	$decimals = $CI->config->item('quantity_decimals') ? $CI->config->item('quantity_decimals') : 0;
	$decimal_point = $CI->config->item('decimal_point') ? $CI->config->item('decimal_point') : '.';

	return number_format($number, $decimals, $decimal_point, '');
}

?>

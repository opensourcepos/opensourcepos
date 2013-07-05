<?php
/** GARRISON MODIFIED 4/20/2013 **/
function to_currency($number)
{
	$CI =& get_instance();
	$currency_symbol = $CI->config->item('currency_symbol') ? $CI->config->item('currency_symbol') : '$';
	if($number >= 0)
	{
		if($CI->config->item('currency_side') !== 'currency_side')
			return $currency_symbol.number_format($number, 2, '.', '');
		else
			return number_format($number, 2, '.', '').$currency_symbol;
	}
    else
    {
    	if($CI->config->item('currency_side') !== 'currency_side')
    		return '-'.$currency_symbol.number_format(abs($number), 2, '.', '');
    	else
    		return '-'.number_format(abs($number), 2, '.', '').$currency_symbol;
    }
}
/** END MODIFIED **/

function to_currency_no_money($number)
{
	return number_format($number, 2, '.', '');
}
?>
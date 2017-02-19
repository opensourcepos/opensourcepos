<?php

class Rounding_code
{
	const HALF_UP = 0;
	const HALF_DOWN = 1;
	const HALF_EVEN = 2;
	const HALF_ODD = 3;
	const ROUND_UP = 4;
	const ROUND_DOWN = 5;
	const HALF_FIVE = 6;

	public static function get_rounding_options()
	{
		$CI =& get_instance();
		$CI->load->helper('language');
		return array(
			Rounding_code::HALF_UP => lang('enum_half_up'),
			Rounding_code::HALF_DOWN => lang('enum_half_down'),
			Rounding_code::HALF_EVEN => lang('enum_half_even'),
			Rounding_code::HALF_ODD => lang('enum_half_odd'),
			Rounding_code::ROUND_UP => lang('enum_round_up'),
			Rounding_code::ROUND_DOWN => lang('enum_round_down'),
			Rounding_code::HALF_FIVE => lang('enum_half_five')
		);
	}

	public static function get_rounding_code_name($rounding_code)
	{
		$CI =& get_instance();
		$CI->load->helper('language');
		if($rounding_code == Rounding_code::HALF_UP)
		{
			return lang('enum_half_up');
		}
		elseif($rounding_code == Rounding_code::HALF_DOWN)
		{
			return lang('enum_half_down');
		}
		elseif($rounding_code == Rounding_code::HALF_EVEN)
		{
			return lang('enum_half_even');
		}
		elseif($rounding_code == Rounding_code::HALF_ODD)
		{
			return lang('enum_half_odd');
		}
		elseif($rounding_code == Rounding_code::ROUND_UP)
		{
			return lang('enum_round_up');
		}
		elseif($rounding_code == Rounding_code::ROUND_DOWN)
		{
			return lang('enum_round_down');
		}
		elseif($rounding_code == Rounding_code::HALF_FIVE)
		{
			return lang('enum_half_five');
		}
		else
		{
			return lang('common_unknown');
		}
	}

	public static function get_html_rounding_options()
	{
		$CI =& get_instance();
		$CI->load->helper('language');
		$x = "<option value='0' selected='selected'>".lang('enum_half_up')."</option>" .
		"<option value='1'>".lang('enum_half_down')."</option>" .
		"<option value='2'>".lang('enum_half_even')."</option>" .
		"<option value='3'>".lang('enum_half_odd')."</option>" .
		"<option value='4'>".lang('enum_round_up')."</option>" .
		"<option value='5'>".lang('enum_round_down')."</option>" .
		"<option value='6'>".lang('enum_half_five')."</option>";

		return $x;
	}
}
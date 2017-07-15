<?php

class Rounding_mode
{
	const HALF_UP = PHP_ROUND_HALF_UP;
	const HALF_DOWN = PHP_ROUND_HALF_DOWN;
	const HALF_EVEN = PHP_ROUND_HALF_EVEN;
	const HALF_ODD = PHP_ROUND_HALF_ODD;
	const ROUND_UP = 5;
	const ROUND_DOWN = 6;
	const HALF_FIVE = 7;

	public static function get_rounding_options()
	{
		$CI =& get_instance();
		$CI->load->helper('language');
		$class = new ReflectionClass(__CLASS__);
		$result = array();
		foreach($class->getConstants() as $key => $value)
		{
			$result[$value] = lang(strtolower('ENUM_'. $key));
		}
		return $result;
	}

	public static function get_rounding_code_name($code)
	{
		$CI =& get_instance();
		$CI->load->helper('language');

		if (empty($code))
		{
			return lang('common_unknown');
		}

		return Rounding_mode::get_rounding_options()[$code];
	}

	public static function get_html_rounding_options()
	{
		$CI =& get_instance();
		$CI->load->helper('language');
		$x = '';
		foreach (Rounding_mode::get_rounding_options() as $option => $label)
		{
			$x .= "<option value='$option'>".$label."</option>";
		}
		return $x;
	}

	public static function round_number($rounding_mode, $amount, $decimals)
	{
		if($rounding_mode == Rounding_mode::ROUND_UP)
		{
			$fig = (int) str_pad('1', $decimals, '0');
			$rounded_total = (ceil($amount * $fig) / $fig);
		}
		elseif($rounding_mode == Rounding_mode::ROUND_DOWN)
		{
			$fig = (int) str_pad('1', $decimals, '0');
			$rounded_total = (floor($amount * $fig) / $fig);
		}
		elseif($rounding_mode == Rounding_mode::HALF_FIVE)
		{
			$rounded_total = round($amount / 5) * 5;
		}
		else
		{
			$rounded_total = round ( $amount, $decimals, $rounding_mode);
		}

		return $rounded_total;
	}
}
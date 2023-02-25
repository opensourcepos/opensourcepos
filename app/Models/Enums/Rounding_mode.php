<?php

namespace App\Models\Enums;

use ReflectionClass;

class Rounding_mode
{
	const HALF_UP = PHP_ROUND_HALF_UP;  //TODO: These constants need to be moved to constants.php
	const HALF_DOWN = PHP_ROUND_HALF_DOWN;
	const HALF_EVEN = PHP_ROUND_HALF_EVEN;
	const HALF_ODD = PHP_ROUND_HALF_ODD;
	const ROUND_UP = 5;
	const ROUND_DOWN = 6;
	const HALF_FIVE = 7;

	public function __construct()
	{
		helper('language');
	}

	public static function get_rounding_options(): array
	{
		$class = new ReflectionClass(__CLASS__);
		$result = [];

		foreach($class->getConstants() as $key => $value)
		{
			$result[$value] = lang('Enum.' . strtolower($key));
		}

		return $result;
	}

	public static function get_rounding_code_name(int $code): string
	{
		if(empty($code))
		{
			return lang('Common.unknown');
		}

		return Rounding_mode::get_rounding_options()[$code];
	}

	public static function get_html_rounding_options(): string
	{
		$x = '';

		foreach(Rounding_mode::get_rounding_options() as $option => $label)
		{
			$x .= "<option value='$option'>".$label."</option>";
		}

		return $x;
	}

	public static function round_number(int $rounding_mode, float $amount, int $decimals): string
	{//TODO: this needs to be replaced with a switch statement
		if($rounding_mode == Rounding_mode::ROUND_UP)
		{
			$fig = pow(10, $decimals);
			$rounded_total = (ceil($fig*$amount) + ceil($fig*$amount - ceil($fig*$amount)))/$fig;
		}
		elseif($rounding_mode == Rounding_mode::ROUND_DOWN)
		{
			$fig = pow(10, $decimals);
			$rounded_total = (floor($fig*$amount) + floor($fig*$amount - floor($fig*$amount)))/$fig;
		}
		elseif($rounding_mode == Rounding_mode::HALF_FIVE)
		{
			$rounded_total = round($amount / 5, $decimals, Rounding_mode::HALF_EVEN) * 5;
		}
		else
		{
			$rounded_total = round ( $amount, $decimals, $rounding_mode);
		}

		return $rounded_total;
	}
}

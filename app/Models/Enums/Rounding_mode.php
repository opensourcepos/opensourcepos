<?php

namespace App\Models\Enums;

use ReflectionClass;

class Rounding_mode
{
	public const HALF_UP = PHP_ROUND_HALF_UP;  //TODO: These constants need to be moved to constants.php
	public const HALF_DOWN = PHP_ROUND_HALF_DOWN;
	public const HALF_EVEN = PHP_ROUND_HALF_EVEN;
	public const HALF_ODD = PHP_ROUND_HALF_ODD;
	public const ROUND_UP = 5;
	public const ROUND_DOWN = 6;
	public const HALF_FIVE = 7;

	public function __construct()
	{
		helper('language');
	}

	/**
	 * @return array
	 */
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

	/**
	 * @param int $code
	 * @return string
	 */
	public static function get_rounding_code_name(int $code): string
	{
		if(empty($code))
		{
			return lang('Common.unknown');
		}

		return Rounding_mode::get_rounding_options()[$code];
	}

	/**
	 * @return string
	 */
	public static function get_html_rounding_options(): string
	{
		$x = '';

		foreach(Rounding_mode::get_rounding_options() as $option => $label)
		{
			$x .= "<option value='$option'>".$label."</option>";
		}

		return $x;
	}

	/**
	 * @param int $rounding_mode
	 * @param float $amount
	 * @param int $decimals
	 * @return string
	 */
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

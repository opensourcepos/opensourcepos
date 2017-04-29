<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Templating\Filters;


abstract class Filters
{

	/**
	 * Calls public method with args if exists and passes args.
	 *
	 * @param string $name
	 * @throws \Exception
	 * @return mixed
	 */
	public function loader($name)
	{
		if (method_exists($this, $name)) {
			$args = array_slice(func_get_args(), 1);
			return call_user_func_array([$this, $name], $args);
		}
		return NULL;
	}


	/**
	 * @param string $string
	 * @return string
	 */
	public static function urlize($string)
	{
		return preg_replace('~[^\w]~', '.', $string);
	}


	/**
	 * @param string $name
	 * @param bool $trimNamespaceSeparator
	 * @return string
	 */
	protected function getTypeName($name, $trimNamespaceSeparator = TRUE)
	{
		$names = [
			'int' => 'integer',
			'bool' => 'boolean',
			'double' => 'float',
			'void' => '',
			'FALSE' => 'false',
			'TRUE' => 'true',
			'NULL' => 'null',
			'callback' => 'callable'
		];

		// Simple type
		if (isset($names[$name])) {
			return $names[$name];
		}

		// Class, constant or function
		return $trimNamespaceSeparator ? ltrim($name, '\\') : $name;
	}


	/**
	 * @param string $url
	 * @return string
	 */
	private function url($url)
	{
		return rawurlencode($url);
	}

}

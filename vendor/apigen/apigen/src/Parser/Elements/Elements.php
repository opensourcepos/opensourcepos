<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Parser\Elements;


class Elements
{

	const CLASSES = 'classes';
	const CONSTANTS = 'constants';
	const EXCEPTIONS = 'exceptions';
	const FUNCTIONS = 'functions';
	const INTERFACES = 'interfaces';
	const TRAITS = 'traits';

	const PROPERTIES = 'properties';
	const METHODS = 'methods';


	/**
	 * @return array
	 */
	public function getClassTypeList()
	{
		return [self::CLASSES, self::EXCEPTIONS, self::INTERFACES, self::TRAITS];
	}


	/**
	 * @return string[]
	 */
	public function getAll()
	{
		return [self::CLASSES, self::CONSTANTS, self::EXCEPTIONS, self::FUNCTIONS, self::INTERFACES, self::TRAITS];
	}


	/**
	 * @return array[]
	 */
	public function getEmptyList()
	{
		$emptyList = [];
		foreach ($this->getAll() as $type) {
			$emptyList[$type] = [];
		}
		return $emptyList;
	}

}

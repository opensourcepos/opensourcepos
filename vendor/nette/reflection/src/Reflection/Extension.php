<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Reflection;

use Nette;


/**
 * Reports information about a extension.
 */
class Extension extends \ReflectionExtension
{
	use Nette\SmartObject;

	public function __toString()
	{
		return $this->getName();
	}


	/********************* Reflection layer ****************d*g**/


	public function getClasses()
	{
		$res = [];
		foreach (parent::getClassNames() as $val) {
			$res[$val] = new ClassType($val);
		}
		return $res;
	}


	public function getFunctions()
	{
		foreach ($res = parent::getFunctions() as $key => $val) {
			$res[$key] = new GlobalFunction($key);
		}
		return $res;
	}

}

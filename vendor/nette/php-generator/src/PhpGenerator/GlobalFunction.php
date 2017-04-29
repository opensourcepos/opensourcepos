<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;


/**
 * Global function.
 *
 * @property string $body
 */
final class GlobalFunction
{
	use Nette\SmartObject;
	use Traits\FunctionLike;
	use Traits\NameAware;
	use Traits\CommentAware;

	/**
	 * @return static
	 */
	public static function from(string $function): self
	{
		return (new Factory)->fromFunctionReflection(new \ReflectionFunction($function));
	}


	public function __toString(): string
	{
		return Helpers::formatDocComment($this->comment . "\n")
			. 'function '
			. ($this->returnReference ? '&' : '')
			. $this->name
			. $this->parametersToString()
			. $this->returnTypeToString()
			. "\n{\n" . Nette\Utils\Strings::indent(ltrim(rtrim($this->body) . "\n"), 1) . '}';
	}

}

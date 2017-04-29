<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;


/**
 * Class property description.
 *
 * @property mixed $value
 */
final class Property
{
	use Nette\SmartObject;
	use Traits\NameAware;
	use Traits\VisibilityAware;
	use Traits\CommentAware;

	/** @var mixed */
	private $value;

	/** @var bool */
	private $static = FALSE;


	/**
	 * @return static
	 */
	public function setValue($val): self
	{
		$this->value = $val;
		return $this;
	}


	public function &getValue()
	{
		return $this->value;
	}


	/**
	 * @return static
	 */
	public function setStatic(bool $state = TRUE): self
	{
		$this->static = $state;
		return $this;
	}


	public function isStatic(): bool
	{
		return $this->static;
	}

}

<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator\Traits;


/**
 * @internal
 */
trait CommentAware
{
	/** @var string|NULL */
	private $comment;


	/**
	 * @param  string|NULL
	 * @return static
	 */
	public function setComment($val): self
	{
		$this->comment = $val ? (string) $val : NULL;
		return $this;
	}


	/**
	 * @return string|NULL
	 */
	public function getComment()
	{
		return $this->comment;
	}


	/**
	 * @return static
	 */
	public function addComment(string $val): self
	{
		$this->comment .= $this->comment ? "\n$val" : $val;
		return $this;
	}

}

<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application\Responses;

use Nette;


/**
 * String output response.
 */
class TextResponse implements Nette\Application\IResponse
{
	use Nette\SmartObject;

	/** @var mixed */
	private $source;


	/**
	 * @param  mixed  renderable variable
	 */
	public function __construct($source)
	{
		$this->source = $source;
	}


	/**
	 * @return mixed
	 */
	public function getSource()
	{
		return $this->source;
	}


	/**
	 * Sends response to output.
	 * @return void
	 */
	public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse)
	{
		if ($this->source instanceof Nette\Application\UI\ITemplate) {
			$this->source->render();

		} else {
			echo $this->source;
		}
	}

}

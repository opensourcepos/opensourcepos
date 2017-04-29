<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application\Responses;

use Nette;


/**
 * JSON response used mainly for AJAX requests.
 */
class JsonResponse implements Nette\Application\IResponse
{
	use Nette\SmartObject;

	/** @var mixed */
	private $payload;

	/** @var string */
	private $contentType;


	/**
	 * @param  mixed   payload
	 * @param  string  MIME content type
	 */
	public function __construct($payload, $contentType = NULL)
	{
		$this->payload = $payload;
		$this->contentType = $contentType ? $contentType : 'application/json';
	}


	/**
	 * @return mixed
	 */
	public function getPayload()
	{
		return $this->payload;
	}


	/**
	 * Returns the MIME content type of a downloaded file.
	 * @return string
	 */
	public function getContentType()
	{
		return $this->contentType;
	}


	/**
	 * Sends response to output.
	 * @return void
	 */
	public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse)
	{
		$httpResponse->setContentType($this->contentType, 'utf-8');
		echo Nette\Utils\Json::encode($this->payload);
	}

}

<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Events;

use ApiGen\Charset\CharsetDetector;
use Kdyby\Events\Subscriber;


class InjectConfig implements Subscriber
{

	/**
	 * @var CharsetDetector
	 */
	private $charsetDetector;


	public function __construct(CharsetDetector $charsetDetector)
	{
		$this->charsetDetector = $charsetDetector;
	}


	/**
	 * @return string[]
	 */
	public function getSubscribedEvents()
	{
		return ['ApiGen\Configuration\Configuration::onOptionsResolve'];
	}


	public function onOptionsResolve(array $config)
	{
		$this->charsetDetector->setCharsets($config['charset']);
	}

}

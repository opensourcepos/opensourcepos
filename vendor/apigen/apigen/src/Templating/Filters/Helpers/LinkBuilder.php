<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Templating\Filters\Helpers;

use Latte\Runtime\Filters;
use Nette\Utils\Html;


class LinkBuilder
{

	/**
	 * @param string $url
	 * @param Html|string $text
	 * @param bool $escape
	 * @param array $classes
	 * @return string
	 */
	public function build($url, $text, $escape = TRUE, array $classes = [])
	{
		return Html::el('a')->href($url)
			->setHtml($escape ? Filters::escapeHtml($text) : $text)
			->addAttributes(['class' => $classes])
			->render();
	}

}

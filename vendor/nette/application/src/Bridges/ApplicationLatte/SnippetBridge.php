<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Bridges\ApplicationLatte;

use Nette;
use Latte\Runtime\ISnippetBridge;
use Nette\Application\UI\Control;
use Nette\Application\UI\IRenderable;


/**
 * @internal
 */
class SnippetBridge implements ISnippetBridge
{
	use Nette\SmartObject;

	/** @var Control */
	private $control;

	/** @var \stdClass|null */
	private $payload;


	public function __construct(Control $control)
	{
		$this->control = $control;
	}


	public function isSnippetMode()
	{
		return $this->control->snippetMode;
	}


	public function setSnippetMode($snippetMode)
	{
		$this->control->snippetMode = $snippetMode;
	}


	public function needsRedraw($name)
	{
		return $this->control->isControlInvalid($name);
	}


	public function markRedrawn($name)
	{
		if ($name !== '') {
			$this->control->redrawControl($name, FALSE);
		}
	}


	public function getHtmlId($name)
	{
		return $this->control->getSnippetId($name);
	}


	public function addSnippet($name, $content)
	{
		if ($this->payload === NULL) {
			$this->payload = $this->control->getPresenter()->getPayload();
		}
		$this->payload->snippets[$this->control->getSnippetId($name)] = $content;
	}


	public function renderChildren()
	{
		$queue = [$this->control];
		do {
			foreach (array_shift($queue)->getComponents() as $child) {
				if ($child instanceof IRenderable) {
					if ($child->isControlInvalid()) {
						$child->snippetMode = TRUE;
						$child->render();
						$child->snippetMode = FALSE;
					}
				} elseif ($child instanceof Nette\ComponentModel\IContainer) {
					$queue[] = $child;
				}
			}
		} while ($queue);
	}

}

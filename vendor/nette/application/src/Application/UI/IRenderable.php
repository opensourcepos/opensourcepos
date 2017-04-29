<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application\UI;


/**
 * Component with ability to repaint.
 */
interface IRenderable
{

	/**
	 * Forces control to repaint.
	 * @return void
	 */
	function redrawControl();

	/**
	 * Is required to repaint the control?
	 * @return bool
	 */
	function isControlInvalid();

}

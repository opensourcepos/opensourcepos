<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application\UI;


/**
 * Defines ITemplate factory.
 */
interface ITemplateFactory
{

	/**
	 * @return ITemplate
	 */
	function createTemplate(Control $control = NULL);

}

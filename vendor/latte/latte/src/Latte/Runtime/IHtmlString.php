<?php

/**
 * This file is part of the Latte (http://latte.nette.org)
 * Copyright (c) 2008 David Grudl (http://davidgrudl.com)
 */

namespace Latte\Runtime;


interface IHtmlString
{

	/**
	 * @return string in HTML format
	 */
	function __toString();

}

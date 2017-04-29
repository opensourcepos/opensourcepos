<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application\UI;


/**
 * Component with ability to save and load its state.
 */
interface IStatePersistent
{

	/**
	 * Loads state informations.
	 * @return void
	 */
	function loadState(array $params);

	/**
	 * Saves state informations for next request.
	 * @return void
	 */
	function saveState(array &$params);

}

<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application;


/**
 * Presenter converts Request to IResponse.
 */
interface IPresenter
{

	/**
	 * @return IResponse
	 */
	function run(Request $request);

}

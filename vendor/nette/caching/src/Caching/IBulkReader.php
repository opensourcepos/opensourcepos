<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Caching;


/**
 * Cache storage with a bulk read support.
 */
interface IBulkReader
{

	/**
	 * Reads from cache in bulk.
	 * @param  string
	 * @return array key => value pairs, missing items are omitted
	 */
	function bulkRead(array $keys);

}

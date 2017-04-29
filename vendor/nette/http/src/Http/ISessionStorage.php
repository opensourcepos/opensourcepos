<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Http;


/**
 * User session storage for PHP < 5.4. @see http://php.net/session_set_save_handler
 *
 * @deprecated since PHP 5.4, use \SessionHandlerInterface
 */
interface ISessionStorage
{

	function open($savePath, $sessionName);

	function close();

	function read($id);

	function write($id, $data);

	function remove($id);

	function clean($maxlifetime);

}

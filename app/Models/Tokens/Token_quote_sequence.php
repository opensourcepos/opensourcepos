<?php

namespace App\Models\Tokens;

use app\Models\Appconfig;
use ReflectionException;

/**
 * Token_quote_sequence class
 *
 * @property appconfig appconfig
 *
 */
class Token_quote_sequence extends Token
{
	public function __construct()
	{
		parent::__construct();
		$this->appconfig = model('AppConfig');

	}

	public function token_id(): string
	{
		return 'QSEQ';
	}

	/**
	 * @throws ReflectionException
	 */
	public function get_value(): string
	{
		return $this->appconfig->acquire_save_next_quote_sequence();
	}
}
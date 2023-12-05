<?php

namespace App\Models\Tokens;

use App\Models\Appconfig;
use ReflectionException;

/**
 * Token_quote_sequence class
 **/
class Token_quote_sequence extends Token
{
	private Appconfig $appconfig;

	public function __construct()
	{
		parent::__construct();
		$this->appconfig = model(AppConfig::class);

	}

	/**
	 * @return string
	 */
	public function token_id(): string
	{
		return 'QSEQ';
	}

	/**
	 * @throws ReflectionException
	 */
	public function get_value(bool $save = true): string
	{
		return $this->appconfig->acquire_next_quote_sequence($save);
	}
}

<?php

namespace App\Models\Tokens;

use App\Models\Appconfig;
use ReflectionException;

/**
 * Token_work_order_sequence class
 **/
class Token_work_order_sequence extends Token
{
	private Appconfig $appconfig;

	public function __construct(string $value = '')
	{
		parent::__construct($value);
		$this->appconfig = model(AppConfig::class);
	}

	public function token_id(): string
	{
		return 'WSEQ';
	}

	/**
	 * @throws ReflectionException
	 */
	public function get_value(bool $save = TRUE): string
	{
		return $this->appconfig->acquire_next_work_order_sequence($save);
	}
}

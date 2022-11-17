<?php

namespace App\Models\Tokens;

use app\Models\Appconfig;
use ReflectionException;

/**
 * Token_work_order_sequence class
 *
 * @property appconfig appconfig
 *
 */
class Token_work_order_sequence extends Token
{
	public function __construct($value = '')
	{
		parent::__construct($value);
		$this->appconfig = model('AppConfig');
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
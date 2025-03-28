<?php

namespace App\Models\Tokens;

use App\Models\Appconfig;
use ReflectionException;

/**
 * Token_invoice_sequence class
 **/

class Token_invoice_sequence extends Token
{
    private Appconfig $appconfig;

    /**
     * @param string $value
     */
    public function __construct(string $value = '')
    {
        parent::__construct($value);
        $this->appconfig = model(Appconfig::class);
    }

    /**
     * @return string
     */
    public function token_id(): string
    {
        return 'ISEQ';
    }

    /**
     * @throws ReflectionException
     */
    public function get_value(bool $save = true): string
    {
        return $this->appconfig->acquire_next_invoice_sequence($save);
    }
}

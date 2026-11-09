<?php

namespace App\Models\Tokens;

use App\Models\Sale;

/**
 * Token_invoice_count class
 *
 * @property sale sale
 *
 */
class Token_invoice_count extends Token
{
    /**
     * @param string $value
     */
    public function __construct(string $value = '')
    {
        parent::__construct($value);
    }

    /**
     * @return string
     */
    public function token_id(): string
    {
        return 'CO';
    }

    /**
     * @return int|string
     */
    public function get_value(): int|string
    {
        $sale = model(Sale::class);
        return empty($this->value) ? $sale->get_invoice_count() : $this->value;
    }
}

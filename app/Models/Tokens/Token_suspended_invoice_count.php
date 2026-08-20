<?php

namespace App\Models\Tokens;

use App\Models\Sale;

/**
 * Token_suspended_invoice_count class
 *
 * @property sale sale
 *
 */
class Token_suspended_invoice_count extends Token
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return string
     */
    public function token_id(): string
    {
        return 'SCO';
    }

    /**
     * @return int
     */
    public function get_value(): int
    {
        $sale = model(Sale::class);
        return $sale->get_suspended_invoice_count();
    }
}

<?php

namespace App\Models\Tokens;

use App\Models\Sale;

/**
 * Token_suspended_invoice_count class
 *
 * @property Sale sale
 */
class Token_suspended_invoice_count extends Token
{
    public function __construct()
    {
        parent::__construct();
    }

    public function token_id(): string
    {
        return 'SCO';
    }

    public function get_value(): int
    {
        $sale = model(Sale::class);

        return $sale->get_suspended_invoice_count();
    }
}

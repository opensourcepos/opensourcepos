<?php

namespace App\Models\Tokens;

use CodeIgniter\Model;

/**
 * Token class
 */
abstract class Token extends Model
{
    protected string $value = '';

    /**
     * @param string $value
     */
    public function __construct(string $value = '')
    {
        parent::__construct();

        $this->value = $value;
    }

    /**
     * @return array
     */
    public static function get_barcode_tokens(): array
    {
        return [
            new Token_barcode_price(),
            new Token_barcode_weight(),
            new Token_barcode_ean()
        ];
    }

    /**
     * @return array
     */
    public static function get_tokens(): array
    {
        return [
            new Token_customer(),
            new Token_invoice_count(),
            new Token_invoice_sequence(),
            new Token_quote_sequence(),
            new Token_suspended_invoice_count(),
            new Token_quote_sequence(),
            new Token_work_order_sequence(),
            new Token_year_invoice_count(),
            new Token_year_quote_count()
        ];
    }

    /**
     * @return string
     */
    abstract public function token_id(): string;

    /**
     * @return mixed
     */
    abstract public function get_value(): mixed;

    /**
     * @param $token_id
     * @return bool
     */
    public function matches($token_id): bool
    {
        return $this->token_id() == $token_id;
    }

    /**
     * @param string $text
     * @return string
     */
    public function replace_token(string $text): string    // TODO: This function is never called in the code
    {
        if (strstr($text, $this->token_id())) {
            return str_replace($this->token_id(), $this->get_value(), $text);
        }

        return $text;
    }
}

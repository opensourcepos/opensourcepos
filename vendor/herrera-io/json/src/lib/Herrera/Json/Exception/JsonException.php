<?php

namespace Herrera\Json\Exception;

/**
 * Used for JSON specific errors.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class JsonException extends Exception
{
    /**
     * The recognized JSON error codes.
     *
     * @var array
     */
    private static $codes = array(
        JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded.',
        JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded.',
        JSON_ERROR_NONE => 'No error has occurred.',
        JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON.',
        JSON_ERROR_SYNTAX => 'Syntax error.',
        JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded.'
    );

    /**
     * The individual JSON error messages.
     *
     * @var array
     */
    private $errors = array();

    /**
     * Sets the main error message, and the other error messages.
     *
     * @param string $message The main error message.
     * @param array  $errors  The other error messages.
     */
    public function __construct($message, array $errors = array())
    {
        $this->errors = $errors;

        parent::__construct($message);
    }

    /**
     * Creates a new exception using the JSON error code.
     *
     * @param integer $code The code.
     *
     * @return JsonException The exception.
     */
    public static function createUsingCode($code)
    {
        $message = 'Unknown error.';

        if (isset(self::$codes[$code])) {
            $message = self::$codes[$code];
        }

        return new static($message);
    }

    /**
     * Returns the other error messages.
     *
     * @return array The messages.
     */
    public function getErrors()
    {
        return $this->errors;
    }
}

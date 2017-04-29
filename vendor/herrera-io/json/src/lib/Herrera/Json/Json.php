<?php

namespace Herrera\Json;

use Herrera\Json\Exception\FileException;
use Herrera\Json\Exception\JsonException;
use JsonSchema\Validator;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;

/**
 * Makes it easier to lint and validate JSON data.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Json
{
    /**
     * The JSON linter.
     *
     * @var JsonParser
     */
    private $linter;

    /**
     * The JSON validator.
     *
     * @var Validator
     */
    private $validator;

    /**
     * Initialize the linter and validator.
     */
    public function __construct()
    {
        $this->linter = new JsonParser();
        $this->validator = new Validator();
    }

    /**
     * Decodes the JSON string and performs a lint check if decoding fails.
     *
     * @param string  $json    The JSON data.
     * @param boolean $assoc   Convert objects to associative arrays?
     * @param integer $depth   The maximum recursion depth.
     * @param integer $options The bitmask JSON decode options (PHP 5.4+).
     *
     * @return mixed The decoded data.
     *
     * @throws Exception\Exception
     * @throws JsonException If the JSON string could not be decoded.
     */
    public function decode($json, $assoc = false, $depth = 512, $options = 0)
    {
        if (JSON_DECODE_FOURTH_ARG) {
            $data = json_decode($json, $assoc, $depth, $options);
        } else {
            $data = json_decode($json, $assoc, $depth);
        }

        if (JSON_ERROR_NONE !== ($error = json_last_error())) {
            if (JSON_ERROR_UTF8 === $error) {
                throw JsonException::createUsingCode($error);
            }

            $this->lint($json);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

        return $data;
    }

    /**
     * Reads the file and decodes the contents using `decode()`.
     *
     * @param string  $file    The file location.
     * @param boolean $assoc   Convert objects to associative arrays?
     * @param integer $depth   The maximum recursion depth.
     * @param integer $options The bitmask JSON decode options (PHP 5.4+).
     *
     * @return mixed The decoded data.
     *
     * @throws Exception\Exception
     * @throws FileException If the file could not be read.
     */
    public function decodeFile(
        $file,
        $assoc = false,
        $depth = 512,
        $options = 0
    ) {
        if ((false == preg_match('/^\w+:\/\//', $file)) &&
            (false === is_file($file))) {
            throw FileException::create(
                'The path "%s" is not a file or does not exist.',
                $file
            );
        }

        if (false === ($json = @file_get_contents($file))) {
            throw FileException::lastError();
        }

        return $this->decode($json, $assoc, $depth, $options);
    }

    /**
     * Lints the JSON string.
     *
     * @param string $json The JSON data.
     *
     * @throws ParsingException If the JSON has lint.
     */
    public function lint($json)
    {
        if (($result = $this->linter->lint($json)) instanceof ParsingException) {
            throw $result;
        }
    }

    /**
     * Validates the decoded JSON data.
     *
     * @param object $schema The JSON schema.
     * @param mixed  $json   The decoded JSON data.
     *
     * @throws Exception\Exception
     * @throws JsonException If the JSON data failed validation.
     */
    public function validate($schema, $json)
    {
        $this->validator->check($json, $schema);

        if (false === $this->validator->isValid()) {
            $errors = array();

            foreach ($this->validator->getErrors() as $error) {
                if (false === empty($error['property'])) {
                    $error['message'] = $error['property']
                                      . ': '
                                      . $error['message'];
                }

                $errors[] = $error['message'];
            }

            throw new JsonException(
                'The JSON data did not pass validation.',
                $errors
            );
        }
    }
}

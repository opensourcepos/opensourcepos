<?php

namespace app\Libraries;

use App\Models\Tokens\Token;
use Config\OSPOS;
use IntlDateFormatter;
use DateTime;

/**
 * Token library
 *
 * Library with utilities to manage tokens
 */
class Token_lib
{
    /**
     * Expands all the tokens found in a given text string and returns the results.
     */
    public function render(string $tokened_text, array $tokens = [], $save = true): string
    {
        // Apply the transformation for the "%" tokens if any are used
        if (strpos($tokened_text, '%') !== false) {
            $tokened_text = strftime($tokened_text);    // TODO: these need to be converted to IntlDateFormatter::format()
        }

        // Call scan to build an array of all of the tokens used in the text to be transformed
        $token_tree = $this->scan($tokened_text);

        if (empty($token_tree)) {
            if (strpos($tokened_text, '%') !== false) {
                return strftime($tokened_text);
            } else {
                return $tokened_text;
            }
        }

        $token_values = [];
        $tokens_to_replace = [];
        $this->generate($token_tree, $tokens_to_replace, $token_values, $save);

        return str_replace($tokens_to_replace, $token_values, $tokened_text);
    }

    /**
     * Expands all the tokens found in a given text string and returns the results.
     * @param string $tokenedText
     * @param array $tokens
     * @param bool $save
     * @return string
     */
    public function renderUpdated(string $tokenedText, array $tokens = [], bool $save = true): string
    {
        // Apply the transformation for the "%" tokens if any are used
        if (str_contains($tokenedText, '%')) {
            $tokenedText = $this->applyDateFormats($tokenedText);
            // replicate error in the original function
            if (str_contains($tokenedText, '%')) {
                return '';
            }
        }

        // Call scan to build an array of all of the tokens used in the text to be transformed
        $token_tree = $this->scan($tokenedText);

        if (empty($token_tree)) {
            if (str_contains($tokenedText, '%')) {
                $tokenedText = $this->applyDateFormats($tokenedText);
                // replicate error in the original function
                if (str_contains($tokenedText, '%')) {
                    return '';
                }
            } else {
                return $tokenedText;
            }
        }

        $token_values = [];
        $tokens_to_replace = [];
        $this->generate($token_tree, $tokens_to_replace, $token_values, $save);

        return str_replace($tokens_to_replace, $token_values, $tokenedText);
    }

    /**
     * Replaces all date formats in a string with the current date in that format. A string that contains no date
     * formats will be returned unchanged.
     * @param string $text
     * @return string
     */
    public function applyDateFormats(string $text): string
    {
        // TODO: get locale from config or user browser
        $formatter = new IntlDateFormatter('en-US', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
        $dateTime = new DateTime();

        return preg_replace_callback(
            "/
            %([a-zA-Z])\1{0,4} # '%' followed by 1-5 of a letter
            [^a-zA-Z]+         # any non-alphabetic separator(s)
            %([a-zA-Z])\2{0,4} # '%' followed by 1-5 of a letter
            [^a-zA-Z]+         # any non-alphabetic separator(s)
            %([a-zA-Z])\3{0,4} # '%' followed by 1-5 of a letter
            (?=[^a-zA-Z]|$)    # any non-alphabetic (or end of string)
        /x",
            function ($match) use ($formatter, $dateTime) {
                // TODO: update this to cover all edge cases between strftime and IntlDateFormatter
                $pattern = str_replace(
                    ['%', 'm', 'B', 'd'],
                    ['', 'M', 'LLLL', 'dd'],
                    $match[0]
                );
                $formatter->setPattern($pattern);
                return $formatter->format($dateTime);
            },
            $text
        );
    }

    /**
     * Parses out the all the tokens enclosed in braces {} and subparses on the colon : character where supplied
     */
    public function scan(string $text): array
    {
        // Matches tokens with the following pattern: [$token:$length]
        preg_match_all('/
                \{             # [ - pattern start
                ([^\s\{\}:]+)  # match $token not containing whitespace : { or }
                (?:
                :              # : - separator
                ([^\s\{\}:]+)     # match $length not containing whitespace : { or }
                )?
                \}             # ] - pattern end
                /x', $text, $matches);

        $tokens = $matches[1];
        $lengths = $matches[2];

        $token_tree = [];
        for ($i = 0; $i < count($tokens); $i++) {
            $token_tree[$tokens[$i]][$lengths[$i]] = $matches[0][$i];
        }

        return $token_tree;
    }

    /**
     * @param string|null $quantity
     * @param string|null $price
     * @param string|null $item_id_or_number_or_item_kit_or_receipt
     * @return void
     */
    public function parse_barcode(?string &$quantity, ?string &$price, ?string &$item_id_or_number_or_item_kit_or_receipt): void
    {
        $config = config(OSPOS::class)->settings;
        $barcode_formats = json_decode($config['barcode_formats']);
        $barcode_tokens = Token::get_barcode_tokens();

        if (!empty($barcode_formats)) {
            foreach ($barcode_formats as $barcode_format) {
                $parsed_results = $this->parse($item_id_or_number_or_item_kit_or_receipt, $barcode_format, $barcode_tokens);
                $quantity = (isset($parsed_results['W'])) ? (int) $parsed_results['W'] / 1000 : 1;
                $item_id_or_number_or_item_kit_or_receipt = (isset($parsed_results['I'])) ?
                    $parsed_results['I'] : $item_id_or_number_or_item_kit_or_receipt;
                $price = (isset($parsed_results['P'])) ? (double) $parsed_results['P'] : null;
            }
        } else {
            $quantity = 1;    // TODO: Quantity is handled using bcmath functions so that it is precision safe.  This should be '1'
        }
    }

    /**
     * @param string $string
     * @param string $pattern
     * @param array $tokens
     * @return array
     */
    public function parse(string $string, string $pattern, array $tokens = []): array    // TODO: $string is a poor name for this parameter.
    {
        $token_tree = $this->scan($pattern);

        $found_tokens = [];
        foreach ($token_tree as $token_id => $token_length) {
            foreach ($tokens as $token) {
                if ($token->token_id() == $token_id) {
                    $found_tokens[] = $token;
                    $keys = array_keys($token_length);
                    $length = array_shift($keys);
                    $pattern = str_replace(array_shift($token_length), "({$token->get_value()}{" . $length . "})", $pattern);
                }
            }
        }

        $results = [];

        if (preg_match("/$pattern/", $string, $matches)) {
            foreach ($found_tokens as $token) {
                $index = array_search($token, $found_tokens);
                $match = $matches[$index + 1];
                $results[$token->token_id()] = $match;
            }
        }

        return $results;
    }

    /**
     * @param array $used_tokens
     * @param array $tokens_to_replace
     * @param array $token_values
     * @param bool $save
     */
    private function generate(array $used_tokens, array &$tokens_to_replace, array &$token_values, bool $save = true): void
    {
        foreach ($used_tokens as $token_code => $token_info) {
            // Generate value here based on the key value
            $token_value = $this->resolve_token($token_code, [], $save);

            foreach ($token_info as $length => $token_spec) {
                $tokens_to_replace[] = $token_spec;
                if (!empty($length)) {
                    $token_values[] = str_pad($token_value, $length, '0', STR_PAD_LEFT);
                } else {
                    $token_values[] = $token_value;
                }
            }
        }
    }

    /**
     * @param $token_code
     * @param array $tokens
     * @param bool $save
     * @return string
     */
    private function resolve_token($token_code, array $tokens = [], bool $save = true): string
    {
        foreach (array_merge($tokens, Token::get_tokens()) as $token) {
            if ($token->token_id() == $token_code) {
                return $token->get_value($save);
            }
        }

        return '';
    }
}

<?php

namespace App\Libraries;

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
    private array $strftimeToIntlPatternMap = [
        '%a' => 'EEE',
        '%A' => 'EEEE',
        '%b' => 'MMM',
        '%B' => 'MMMM',
        '%d' => 'dd',
        '%D' => 'MM/dd/yy',
        '%e' => 'd',
        '%F' => 'yyyy-MM-dd',
        '%h' => 'MMM',
        '%j' => 'D',
        '%m' => 'MM',
        '%U' => 'w',
        '%V' => 'ww',
        '%W' => 'ww',
        '%y' => 'yy',
        '%Y' => 'yyyy',
        '%H' => 'HH',
        '%I' => 'hh',
        '%l' => 'h',
        '%M' => 'mm',
        '%p' => 'a',
        '%P' => 'a',
        '%r' => 'hh:mm:ss a',
        '%R' => 'HH:mm',
        '%S' => 'ss',
        '%T' => 'HH:mm:ss',
        '%X' => 'HH:mm:ss',
        '%z' => 'ZZZZZ',
        '%Z' => 'z',
        '%g' => 'yy',
        '%G' => 'yyyy',
        '%u' => 'e',
        '%w' => 'c',
    ];

    private array $validStrftimeFormats = [
        'a', 'A', 'b', 'B', 'c', 'd', 'D', 'e', 'F', 'g', 'G',
        'h', 'H', 'I', 'j', 'm', 'M', 'n', 'p', 'P', 'r', 'R',
        'S', 't', 'T', 'u', 'U', 'V', 'w', 'W', 'x', 'X', 'y', 'Y', 'z', 'Z'
    ];

    /**
     * Expands all the tokens found in a given text string and returns the results.
     */
    public function render(string $tokened_text, array $tokens = [], $save = true): string
    {
        if (str_contains($tokened_text, '%')) {
            $tokened_text = $this->applyDateFormats($tokened_text);
        }

        $token_tree = $this->scan($tokened_text);

        if (empty($token_tree)) {
            return $tokened_text;
        }

        $token_values = [];
        $tokens_to_replace = [];
        $this->generate($token_tree, $tokens, $tokens_to_replace, $token_values, $save);

        return str_replace($tokens_to_replace, $token_values, $tokened_text);
    }

    private function applyDateFormats(string $text): string
    {
        $formatter = new IntlDateFormatter(
            null,
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            null,
            null,
            ''
        );

        $dateTime = new DateTime();

        return preg_replace_callback(
            '/%([a-zA-Z%])/',
            function ($match) use ($formatter, $dateTime) {
                $formatChar = $match[1];

                if ($formatChar === '%') {
                    return '%';
                }

                if ($formatChar === 'n') {
                    return "\n";
                }

                if ($formatChar === 't') {
                    return "\t";
                }

                if ($formatChar === 'C') {
                    return str_pad((string) intdiv((int) $dateTime->format('Y'), 100), 2, '0', STR_PAD_LEFT);
                }

                if ($formatChar === 'c') {
                    $formatter->setPattern('yyyy-MM-dd HH:mm:ss');
                    $result = $formatter->format($dateTime);
                    return $result !== false ? $result : $match[0];
                }

                if ($formatChar === 'x') {
                    $formatter->setPattern('yyyy-MM-dd');
                    $result = $formatter->format($dateTime);
                    return $result !== false ? $result : $match[0];
                }

                if (!in_array($formatChar, $this->validStrftimeFormats, true)) {
                    return $match[0];
                }

                $intlPattern = $this->strftimeToIntlPatternMap[$match[0]] ?? null;

                if ($intlPattern === null) {
                    return $match[0];
                }

                $formatter->setPattern($intlPattern);
                $result = $formatter->format($dateTime);

                return $result !== false ? $result : $match[0];
            },
            $text
        );
    }

    public function scan(string $text): array
    {
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
            $quantity = 1;
        }
    }

    public function parse(string $string, string $pattern, array $tokens = []): array
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

    private function generate(array $used_tokens, array $tokens, array &$tokens_to_replace, array &$token_values, bool $save = true): void
    {
        foreach ($used_tokens as $token_code => $token_info) {
            $token_value = $this->resolve_token($token_code, $tokens, $save);

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
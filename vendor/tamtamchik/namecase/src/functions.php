<?php

use Tamtamchik\NameCase\Formatter;

if ( ! function_exists('str_name_case')) {

    /**
     * Wrapper for NameCase object to be used as global function.
     *
     * @param string $string  - string to NameCase.
     * @param array  $options - options for NameCase.
     *
     * @return string
     */
    function str_name_case($string, $options = [])
    {
        return Formatter::nameCase($string, $options);
    }
}

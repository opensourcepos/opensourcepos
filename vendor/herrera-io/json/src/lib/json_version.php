<?php

if (!defined('JSON_DECODE_FOURTH_ARG')) {

    /**
     * The flag used to control the use of the fourth argument for json_decode().
     *
     * @var boolean
     */
    define('JSON_DECODE_FOURTH_ARG', version_compare(phpversion(), '5.4.0', '>='));

}

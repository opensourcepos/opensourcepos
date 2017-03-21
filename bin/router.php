<?php
/**
 * Part of CodeIgniter Composer Installer
 *
 * @author     Kenji Suzuki <https://github.com/kenjis>
 * @license    MIT License
 * @copyright  2015 Kenji Suzuki
 * @link       https://github.com/kenjis/codeigniter-composer-installer
 */

/**
 * Router script for PHP built-in server
 */
$_SERVER = array_merge($_SERVER, $_ENV);

$file = $_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME'];
//echo $file, PHP_EOL;

if (is_file($file)) {
    return false;
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $_SERVER['DOCUMENT_ROOT'] . '/index.php';
//echo $_SERVER['SCRIPT_FILENAME'], PHP_EOL;

chdir($_SERVER['DOCUMENT_ROOT']);
require $_SERVER['SCRIPT_FILENAME'];

<?php

use Tamtamchik\NameCase\Formatter;

require_once __DIR__ . '/../vendor/autoload.php';

// As global function
echo 'KEITH => ' . str_name_case('KEITH') . PHP_EOL;
echo 'LEIGH-WILLIAMS => ' . str_name_case('LEIGH-WILLIAMS') . PHP_EOL;
echo 'MCCARTHY => ' . str_name_case('MCCARTHY') . PHP_EOL;
echo 'O\'CALLAGHAN => ' . str_name_case('O\'CALLAGHAN') . PHP_EOL;
echo 'ST. JOHN => ' . str_name_case('ST. JOHN') . PHP_EOL;
echo 'VON STREIT => ' . str_name_case('VON STREIT') . PHP_EOL;
echo 'AP LLWYD DAFYDD => ' . str_name_case('AP LLWYD DAFYDD') . PHP_EOL;
echo 'HENRY VIII => ' . str_name_case('HENRY VIII') . PHP_EOL;

// As static call
echo 'VAN DYKE => ' . Formatter::nameCase('VAN DYKE') . PHP_EOL;

// As instance
$formatter = new Formatter();
echo 'LOUIS XIV => ' . $formatter->nameCase('LOUIS XIV') . PHP_EOL;

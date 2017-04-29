<?php

require __DIR__ . '/../src/tracy.php';

use Tracy\Debugger;

$arr = [10, 20, ['key1' => 'val1', 'key2' => TRUE]];

// will show in FireLogger
Debugger::fireLog('Hello World');
Debugger::fireLog($arr);


function first($arg1, $arg2)
{
	second(TRUE, FALSE);
}

function second($arg1, $arg2)
{
	third([1, 2, 3]);
}

function third($arg1)
{
	throw new Exception('The my exception', 123);
}

try {
	first(10, 'any string');
} catch (Exception $e) {
	Debugger::fireLog($e);
}

?>
<!DOCTYPE html><link rel="stylesheet" href="assets/style.css">

<h1>Tracy: FireLogger demo</h1>

<p>How to <a href="https://tracy.nette.org/#toc-firelogger">enable FireLogger</a>?</p>

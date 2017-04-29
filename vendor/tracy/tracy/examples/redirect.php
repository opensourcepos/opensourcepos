<?php

require __DIR__ . '/../src/tracy.php';

use Tracy\Debugger;

session_start(); // session is required for this functionality
Debugger::enable(Debugger::DETECT, __DIR__ . '/log');


if (empty($_GET['redirect'])) {
	Debugger::barDump('before redirect');
	header('Location: ' . $_SERVER['REQUEST_URI'] . '?&redirect=1');
	exit;
}

Debugger::barDump('after redirect');

?>
<!DOCTYPE html><html class=arrow><link rel="stylesheet" href="assets/style.css">

<h1>Tracy: redirect demo</h1>

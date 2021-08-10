<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

An uncaught Exception was encountered

Type:        <?= get_class($exception), "\n"; ?>
Message:     <?= $message, "\n"; ?>
Filename:    <?= $exception->getFile(), "\n"; ?>
Line Number: <?= $exception->getLine(); ?>

<?php if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === TRUE): ?>

Backtrace:
<?php	foreach ($exception->getTrace() as $error): ?>
<?php		if (isset($error['file']) && strpos($error['file'], realpath(BASEPATH)) !== 0): ?>
	File: <?= $error['file'], "\n"; ?>
	Line: <?= $error['line'], "\n"; ?>
	Function: <?= $error['function'], "\n\n"; ?>
<?php		endif ?>
<?php	endforeach ?>

<?php endif ?>

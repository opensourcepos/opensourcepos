<?php
/**
 * @var string $severity
 * @var string $message
 * @var string $filepath
 * @var int $line
 */
?>
A PHP Error was encountered

Severity:    <?= esc($severity), "\n" ?>
Message:     <?= esc($message), "\n" ?>
Filename:    <?= esc($filepath), "\n" ?>
Line Number: <?= $line ?>

<?php if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE): ?>

Backtrace:
<?php	foreach (debug_backtrace() as $error): ?>
<?php		if (isset($error['file']) && strpos($error['file'], realpath(ROOTPATH)) !== 0): ?>
	File: <?= esc($error['file']), "\n" ?>
	Line: <?= $error['line'], "\n" ?>
	Function: <?= esc($error['function']), "\n\n" ?>
<?php		endif ?>
<?php	endforeach ?>

<?php endif ?>

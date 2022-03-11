<?php
/**
 * @var string $severity
 * @var string $message
 * @var string $filepath
 * @var int $line
 */
?>
A PHP Error was encountered

Severity:    <?php echo esc($severity), "\n" ?>
Message:     <?php echo esc($message), "\n" ?>
Filename:    <?php echo esc($filepath), "\n" ?>
Line Number: <?php echo $line ?>

<?php if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === TRUE): ?>

Backtrace:
<?php	foreach (debug_backtrace() as $error): ?>
<?php		if (isset($error['file']) && strpos($error['file'], realpath(ROOTPATH)) !== 0): ?>
	File: <?php echo esc($error['file']), "\n" ?>
	Line: <?php echo $error['line'], "\n" ?>
	Function: <?php echo esc($error['function']), "\n\n" ?>
<?php		endif ?>
<?php	endforeach ?>

<?php endif ?>

<?php
/**
 * @var object $exception
 * @var string $message
 */
?>
An uncaught Exception was encountered

Type:        <?php echo get_class($exception), "\n" ?>
Message:     <?php echo esc($message), "\n" ?>
Filename:    <?php echo esc($exception->getFile()), "\n" ?>
Line Number: <?php echo $exception->getLine() ?>

<?php if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === TRUE): ?>

Backtrace:
<?php	foreach ($exception->getTrace() as $error): ?>
<?php		if (isset($error['file']) && strpos($error['file'], realpath(ROOTPATH)) !== 0): ?>
	File: <?php echo esc($error['file']), "\n" ?>
	Line: <?php echo $error['line'], "\n" ?>
	Function: <?php echo esc($error['function']), "\n\n" ?>
<?php		endif ?>
<?php	endforeach ?>

<?php endif ?>

<?php
/**
 * @var string $severity
 * @var string $message
 * @var string $filepath
 * @var int $line
 */
?>

<div style="border: 1px solid #990000; padding-left: 20px; margin: 0 0 10px 0;">

    <h4>A PHP Error was encountered</h4>

    <p>Severity: <?= esc($severity) ?></p>
    <p>Message: <?= esc($message) ?></p>
    <p>Filename: <?= esc($filepath) ?></p>
    <p>Line Number: <?= $line ?></p>

    <?php if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE): ?>

        <p>Backtrace:</p>
        <?php foreach (debug_backtrace() as $error): ?>

            <?php if (isset($error['file']) && strpos($error['file'], realpath(ROOTPATH)) !== 0): ?>

                <p style="margin-left: 10px;">
                    File: <?= $error['file'] ?><br>
                    Line: <?= $error['line'] ?><br>
                    Function: <?= $error['function'] ?>
                </p>

            <?php endif ?>

        <?php endforeach ?>

    <?php endif ?>

</div>

<?php

namespace App\Logging;

use CodeIgniter\Log\Handlers\FileHandler;
use DateTime;

/**
 * Writes job queue log entries to a per-queue file (jobsLog-{queue}-{date}.log)
 * instead of CI4 FileHandler's shared log-{date}.log, so routine job activity
 * doesn't clog the main application log.
 */
class JobsFileHandler extends FileHandler
{
    public function __construct(array $config, private readonly string $queueName)
    {
        parent::__construct($config);
    }

    public function handle($level, $message): bool
    {
        $filepath = $this->path . 'jobsLog-' . $this->queueName . '-' . date('Y-m-d') . '.' . $this->fileExtension;

        $msg = '';

        $newfile = false;
        if (! is_file($filepath)) {
            $newfile = true;

            if ($this->fileExtension === 'php') {
                $msg .= "<?php defined('SYSTEMPATH') || exit('No direct script access allowed'); ?>\n\n";
            }
        }

        if (! $fp = @fopen($filepath, 'ab')) {
            return false;
        }

        if (str_contains($this->dateFormat, 'u')) {
            $microtimeFull = microtime(true);
            $microtimeShort = sprintf('%06d', ($microtimeFull - floor($microtimeFull)) * 1_000_000);
            $date = new DateTime(date('Y-m-d H:i:s.' . $microtimeShort, (int)$microtimeFull));
            $date = $date->format($this->dateFormat);
        } else {
            $date = date($this->dateFormat);
        }

        $msg .= strtoupper($level) . ' - ' . $date . ' --> ' . $message . "\n";

        flock($fp, LOCK_EX);

        $result = null;

        for ($written = 0, $length = strlen($msg); $written < $length; $written += $result) {
            if (($result = fwrite($fp, substr($msg, $written))) === false) {
                break;
            }
        }

        flock($fp, LOCK_UN);
        fclose($fp);

        if ($newfile) {
            @chmod($filepath, $this->filePermissions);
        }

        return is_int($result);
    }
}

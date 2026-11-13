<?php

use App\Logging\JobsFileHandler;
use Config\Logger as LoggerConfig;

if (! function_exists('job_log')) {
    /**
     * Logs a job queue message to its own per-queue file
     * (writable/logs/jobsLog-{queueName}-{date}.log) instead of the main
     * application log, keeping routine job activity out of it.
     */
    function job_log(string $queueName, string $level, string $message): void
    {
        static $handlers = [];

        if (! isset($handlers[$queueName])) {
            $handlers[$queueName] = new JobsFileHandler([
                'handles' => [
                    'critical', 'alert', 'emergency', 'debug',
                    'error', 'info', 'notice', 'warning',
                ],
                'path' => '',
                'fileExtension' => '',
                'filePermissions' => 0660,
            ], $queueName);
        }

        $handlers[$queueName]->setDateFormat(config(LoggerConfig::class)->dateFormat)->handle($level, $message);
    }
}

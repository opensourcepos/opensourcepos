<?php

namespace App\Libraries\Plugins;

class PluginLogger
{
    private string $basePath;
    private string $dateFormat;

    public function __construct(string $basePath = '', string $dateFormat = 'Y-m-d H:i:s')
    {
        $this->basePath   = $basePath !== '' ? $basePath : WRITEPATH . 'logs/';
        $this->dateFormat = $dateFormat;
    }

    public function log(string $pluginId, string $level, string $message, ?string $logName = null): void
    {
        $filepath = $this->basePath . $this->buildFilename($pluginId, $logName);
        $this->write($filepath, $level, $message);
    }

    public function getLogPath(string $pluginId, ?string $logName = null): string
    {
        return $this->basePath . $this->buildFilename($pluginId, $logName);
    }

    private function sanitize(string $id): string
    {
        return strtolower(preg_replace('/[^a-zA-Z0-9-]/', '-', $id));
    }

    private function buildFilename(string $pluginId, ?string $logName): string
    {
        $id   = $this->sanitize($pluginId);
        $date = date('Y-m-d');

        if ($logName !== null && $logName !== '') {
            return 'plugin-' . $id . '-' . $this->sanitize($logName) . '-' . $date . '.log';
        }

        return 'plugin-' . $id . '-' . $date . '.log';
    }

    private function write(string $filepath, string $level, string $message): void
    {
        $newFile = !is_file($filepath);

        $fp = @fopen($filepath, 'ab');

        if ($fp === false) {
            log_message('warning', 'PluginLogger: could not open log file: ' . $filepath);
            return;
        }

        $date = date($this->dateFormat);
        $line = strtoupper($level) . ' - ' . $date . ' --> ' . $message . "\n";

        flock($fp, LOCK_EX);

        $result = null;
        for ($written = 0, $length = strlen($line); $written < $length; $written += $result) {
            $result = fwrite($fp, substr($line, $written));
            if ($result === false) {
                break;
            }
        }

        flock($fp, LOCK_UN);
        fclose($fp);

        if ($newFile) {
            chmod($filepath, 0660);
        }
    }
}

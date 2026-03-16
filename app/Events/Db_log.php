<?php

namespace App\Events;

use Config\Database;
use Config\App;

class Db_log
{
    private App $config;

    /**
     * @return void
     */
    public function db_log_queries(): void
    {
        $this->config = config('App');

        if ($this->config->db_log_enabled) {
            $filepath = WRITEPATH . 'logs/Query-log-' . date('Y-m-d') . '.log';
            $handle = fopen($filepath, "a+");
            $message = $this->generate_message();

            if (strlen($message) > 0) {
                fwrite($handle, $message . "\n\n");
            }

            // Close the file
            fclose($handle);
        }
    }

    /**
     * @return string
     */
    private function generate_message(): string
    {
        $db = Database::connect();
        $lastQuery = $db->getLastQuery();

        if ($lastQuery === null) {
            return '';
        }

        $affectedRows = $db->affectedRows();
        $executionTime = $this->convert_time($lastQuery->getDuration());

        $message = '*** Query: ' . date('Y-m-d H:i:s T') . ' *******************'
            . "\n" . $lastQuery->getQuery()
            . "\n Affected rows: $affectedRows"
            . "\n Execution Time: " . $executionTime['time'] . ' ' . $executionTime['unit'];

        $longQuery = ($executionTime['unit'] === 's') && ($executionTime['time'] > 0.5);
        if ($longQuery) {
            $message .= ' [LONG RUNNING QUERY]';
        }

        return $this->config->db_log_only_long && !$longQuery ? '' : $message;
    }

    /**
     * @param float $time
     * @return array
     */
    private function convert_time(float $time): array
    {
        $unit = 's';

        if ($time <= 0.1 && $time > 0.0001) {
            $time = $time * 1000;
            $unit = 'ms';
        } elseif ($time <= 0.0001) {
            $time = $time * 1000000;
            $unit = 'µs';
        }

        return ['time' => $time, 'unit' => $unit];
    }
}

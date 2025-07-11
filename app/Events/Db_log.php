<?php

namespace App\Events;

use Config\App;
use Config\Database;

class Db_log
{
    private App $config;

    public function db_log_queries(): void
    {
        $this->config = config('App');

        if ($this->config->db_log_enabled) {
            $filepath = WRITEPATH . 'logs/Query-log-' . date('Y-m-d') . '.log';
            $handle   = fopen($filepath, 'a+b');
            $message  = $this->generate_message();

            if ($message !== '') {
                fwrite($handle, $message . "\n\n");
            }

            // Close the file
            fclose($handle);
        }
    }

    private function generate_message(): string
    {
        $db             = Database::connect();
        $last_query     = $db->getLastQuery();
        $affected_rows  = $db->affectedRows();
        $execution_time = $this->convert_time($last_query->getDuration());

        $message = '*** Query: ' . date('Y-m-d H:i:s T') . ' *******************'
            . "\n" . $last_query->getQuery()
            . "\n Affected rows: {$affected_rows}"
            . "\n Execution Time: " . $execution_time['time'] . ' ' . $execution_time['unit'];

        $long_query = ($execution_time['unit'] === 's') && ($execution_time['time'] > 0.5);
        if ($long_query) {
            $message .= ' [LONG RUNNING QUERY]';
        }

        return $this->config->db_log_only_long && ! $long_query ? '' : $message;
    }

    private function convert_time(float $time): array
    {
        $unit = 's';

        if ($time <= 0.1 && $time > 0.0001) {
            $time *= 1000;
            $unit = 'ms';
        } elseif ($time <= 0.0001) {
            $time *= 1000000;
            $unit = 'µs';
        }

        return ['time' => $time, 'unit' => $unit];
    }
}

<?php

namespace App\Events;

use Config\Database;

class Db_log
{
	public function db_log_queries(): void
	{
		$config = config('App');
		// check if database logging is enabled (see config/config.php)
		if($config->db_log_enabled)
		{
			$db = Database::connect();

			$filepath = WRITEPATH . 'logs/Query-log-' . date('Y-m-d') . '.log';
			$handle = fopen($filepath, "a+");

			$sql = $db->getLastQuery();
			$execution_time = $this->convert_time($sql->getDuration());
			$sql .= " \n Affected rows: " . $db->affectedRows()
				. " \n Execution Time: " . $execution_time['time'] . ' ' . $execution_time['unit'];
			fwrite($handle, $sql . "\n\n");

			// Close the file
			fclose($handle);
		}
	}

	private function convert_time(float $time): array
	{
		$unit = 's';

		if($time <= 0.1 && $time > 0.0001)
		{
			$time = $time * 1000;
			$unit = 'ms';
		}
		elseif($time <= 0.0001)
		{
			$time = $time * 1000000;
			$unit = 'µs';
		}

		return ['time' => $time, 'unit' => $unit];
	}
}

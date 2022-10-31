<?php

namespace App\Events;

class Db_log
{
	public function db_log_queries()
	{
		$config = config('App');

		// check if database logging is enabled (see config/config.php)
		if($config->db_log_enabled)
		{
			// Creating Query Log file with today's date in application/logs folder
			$filepath = WRITEPATH . 'logs/Query-log-' . date('Y-m-d') . '.php';
			// Opening file with pointer at the end of the file
			$handle = fopen($filepath, "a+");

			// Get execution time of all the queries executed by controller
			$times = $config->db->query_times;
			foreach($config->db->queries as $key => $query)
			{
				// Generating SQL file along with execution time
				$sql = $query . " \n Execution Time:" . $times[$key];
				// Writing it in the log file
				fwrite($handle, $sql . "\n\n");
			}

			// Close the file
			fclose($handle);
		}
	}
}
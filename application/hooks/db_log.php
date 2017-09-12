<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Name of function same as mentioned in Hooks Config
 */
function db_log_queries()
{
	$CI = & get_instance();

	// check if database logging is enabled (see config/config.php)
	if($CI->config->item('db_log_enabled'))
	{
		// Creating Query Log file with today's date in application/logs folder
		$filepath = APPPATH . 'logs/Query-log-' . date('Y-m-d') . '.php';
		// Opening file with pointer at the end of the file
		$handle = fopen($filepath, "a+");

		// Get execution time of all the queries executed by controller
		$times = $CI->db->query_times;
		foreach($CI->db->queries as $key => $query)
		{ 
			// Generating SQL file alongwith execution time
			$sql = $query . " \n Execution Time:" . $times[$key];
			// Writing it in the log file
			fwrite($handle, $sql . "\n\n");
		}

		// Close the file
		fclose($handle);
	}
}

?>

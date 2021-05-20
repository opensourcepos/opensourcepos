<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Migration helper
 */

function execute_script($path)
{
	$CI =& get_instance();

	$version = preg_replace("/(.*_)?(.*).sql/", "$2", $path);
	error_log("Migrating to $version (file: $path)");

	$sql = file_get_contents($path);

	/*
	CI migration only allows you to run one statement at a time.
	This small script splits the statements allowing you to run them all in one go.
	*/

	$sqls = explode(';', $sql);
	array_pop($sqls);

	foreach($sqls as $statement)
	{
		$statement = $statement . ';';

		if(!$CI->db->simple_query($statement))
		{
			foreach($CI->db->error() as $error)
			{
				error_log('error: ' . $error);
			}
		}
	}

	error_log("Migrated to $version");
}

?>

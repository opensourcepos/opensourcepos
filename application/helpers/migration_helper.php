<?php

function execute_script($path)
{
	$version = preg_replace("/(.*_)?(.*).sql/", "$2", $path);
	error_log("Migrating to $version");

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

		if(!$this->db->simple_query($statement))
		{
			foreach($this->db->error() as $error)
			{
				error_log('error: ' . $error);
			}
		}
	}

	error_log("Migrated to $version");
}
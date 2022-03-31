<?php

use Config\Database;

/**
 * Migration helper
 */
function execute_script(string $path): void
{
	$version = preg_replace("/(.*_)?(.*).sql/", "$2", $path);
	error_log("Migrating to $version (file: $path)");

	$sql = file_get_contents($path);

	/*
	CI migration only allows you to run one statement at a time.
	This small script splits the statements allowing you to run them all in one go.
	*/

	$sqls = explode(';', $sql);
	array_pop($sqls);

	$db = Database::connect();

	foreach($sqls as $statement)
	{
		$statement = $statement . ';';	//TODO: Can use string interpolation here

		if(!$db->simpleQuery($statement))
		{
			foreach($db->error() as $error)
			{
				error_log('error: ' . $error);	//TODO: Can use string interpolation here
			}
		}
	}

	error_log("Migrated to $version");
}
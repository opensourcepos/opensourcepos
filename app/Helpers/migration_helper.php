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
	$sqls = explode(';', $sql);
	array_pop($sqls);

	$db = Database::connect();

	foreach($sqls as $statement)
	{
		$statement = "$statement;";

		if(!$db->simpleQuery($statement))
		{
			foreach($db->error() as $error)
			{
				error_log("error: $error");
			}
		}
	}

	error_log("Migrated to $version");
}
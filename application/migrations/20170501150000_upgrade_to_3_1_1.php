<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Upgrade_To_3_1_1 extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		error_log('Migrating to 3.1.1');

		$sql = file_get_contents(APPPATH . 'migrations/sqlscripts/3.0.2_to_3.1.1.sql');

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

		error_log('Migrated to 3.1.1');
	}

	public function down()
	{

	}
}
?>

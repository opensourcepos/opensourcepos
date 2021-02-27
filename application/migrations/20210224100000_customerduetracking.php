<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_customerduetracking extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		$this->db->query('ALTER TABLE ' . $this->db->dbprefix('customers') . ' ADD COLUMN `credit_limit` decimal(15,2) NOT NULL DEFAULT 0 AFTER `discount_type`');
	}

	public function down()
	{
		$this->db->query('ALTER TABLE ' . $this->db->dbprefix('customers') . ' DROP COLUMN `credit_limit`');
	}
}
?>

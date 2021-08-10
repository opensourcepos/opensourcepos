<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_cashrounding extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		$this->db->query('ALTER TABLE ' . $this->db->dbprefix('sales_payments') . ' ADD COLUMN `cash_adjustment` tinyint NOT NULL DEFAULT 0 AFTER `cash_refund`');
	}

	public function down()
	{
		$this->db->query('ALTER TABLE ' . $this->db->dbprefix('sales_payments') . ' DROP COLUMN `cash_adjustment`');
	}
}
?>

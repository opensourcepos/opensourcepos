<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Supplier_Category extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		$query	= "ALTER TABLE `ospos_suppliers` ";
		$query .= "ADD COLUMN `category` TINYINT NOT NULL";
		$this->db->query($query);
		$query	= "UPDATE `ospos_suppliers` ";
		$query .= "SET `category` = 0";
		$this->db->query($query);
	}

	public function down()
	{

	}
}

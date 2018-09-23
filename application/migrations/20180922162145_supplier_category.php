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
		$query .= "ADD COLUMN `category` VARCHAR(50) NOT NULL";
		$this->db->query($query);
		$category = $this->lang->line('suppliers_goods');
		$query	= "UPDATE `ospos_suppliers` ";
		$query .= "SET `category` = '" . $category . "'";
		$this->db->query($query);
	}

	public function down()
	{

	}
}

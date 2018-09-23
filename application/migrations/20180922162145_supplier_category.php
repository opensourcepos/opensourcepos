<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Supplier_Category extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		$category = $this->lang->line('suppliers_goods');
		$query	= "ALTER TABLE `ospos_suppliers` ";
		$query .= "ADD COLUMN `category` VARCHAR(50) NOT NULL DEFAULT '" . $category . "'";
		$this->db->query($query);
	}

	public function down()
	{

	}
}

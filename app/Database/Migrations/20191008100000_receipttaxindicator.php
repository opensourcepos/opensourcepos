<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_receipttaxindicator extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		$this->db->query('INSERT INTO ' . $this->db->dbprefix('app_config') . ' (`key`, `value`)
			VALUES (\'receipt_show_tax_ind\', \'0\')');
	}

	public function down()
	{
		$this->db->query('DELETE FROM ' . $this->db->dbprefix('app_config') . ' WHERE key = \'receipt_show_tax_ind\'');
	}
}
?>

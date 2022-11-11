<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_taxgroupconstraint extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		$this->db->query('ALTER TABLE ' . $this->db->dbprefix('tax_jurisdictions') . ' ADD CONSTRAINT tax_jurisdictions_uq1 UNIQUE (tax_group)');
	}

	public function down()
	{
		$this->db->query('ALTER TABLE ' . $this->db->dbprefix('tax_jurisdictions') . ' DROP INDEX tax_jurisdictions_uq1');
	}
}
?>

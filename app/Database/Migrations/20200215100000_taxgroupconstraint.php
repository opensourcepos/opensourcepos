<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_taxgroupconstraint extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        $this->db->query('ALTER TABLE ' . $this->db->prefixTable('tax_jurisdictions') . ' ADD CONSTRAINT tax_jurisdictions_uq1 UNIQUE (tax_group)');
    }

    /**
     * Revert a migration step.
     */
    public function down(): void
    {
        $this->db->query('ALTER TABLE ' . $this->db->prefixTable('tax_jurisdictions') . ' DROP INDEX tax_jurisdictions_uq1');
    }
}

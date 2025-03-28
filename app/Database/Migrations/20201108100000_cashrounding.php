<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_cashrounding extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        $this->db->query('ALTER TABLE ' . $this->db->prefixTable('sales_payments') . ' ADD COLUMN `cash_adjustment` tinyint NOT NULL DEFAULT 0 AFTER `cash_refund`');
    }

    /**
     * Revert a migration step.
     */
    public function down(): void
    {
        $this->db->query('ALTER TABLE ' . $this->db->prefixTable('sales_payments') . ' DROP COLUMN `cash_adjustment`');
    }
}

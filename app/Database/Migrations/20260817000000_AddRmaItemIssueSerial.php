<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRmaItemIssueSerial extends Migration
{
    public function up(): void
    {
        $this->db->query('ALTER TABLE ' . $this->db->prefixTable('rma_items') . '
            ADD COLUMN `issue` text NULL AFTER `description`,
            ADD COLUMN `serial_number` varchar(255) NOT NULL DEFAULT \'\' AFTER `issue`');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE ' . $this->db->prefixTable('rma_items') . '
            DROP COLUMN `serial_number`,
            DROP COLUMN `issue`');
    }
}

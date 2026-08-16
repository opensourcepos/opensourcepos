<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBrandToItems extends Migration
{
    public function up(): void
    {
        $this->db->query('ALTER TABLE ' . $this->db->prefixTable('items') . '
            ADD COLUMN `brand` varchar(255) NOT NULL DEFAULT \'\' AFTER `name`');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE ' . $this->db->prefixTable('items') . '
            DROP COLUMN `brand`');
    }
}

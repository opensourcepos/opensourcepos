<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPersonAttributeFlag extends Migration
{
    public function up(): void
    {
        $this->db->query('ALTER TABLE `ospos_attribute_definitions` ADD COLUMN `person_attribute` TINYINT(1) DEFAULT 0 AFTER `definition_flags`');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE `ospos_attribute_definitions` DROP COLUMN `person_attribute`');
    }
}
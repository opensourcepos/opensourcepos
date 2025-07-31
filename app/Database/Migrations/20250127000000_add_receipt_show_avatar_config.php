<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReceiptShowAvatarConfig extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        $config_values = [
            ['key' => 'receipt_show_avatar', 'value' => '0']
        ];

        $this->db->table('app_config')->ignore(true)->insertBatch($config_values);
    }

    /**
     * Revert a migration step.
     */
    public function down(): void
    {
        $this->db->table('app_config')->where('key', 'receipt_show_avatar')->delete();
    }
}
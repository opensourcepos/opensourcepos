<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_add_turnstile_config extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        $turnstile_values = [
            ['key' => 'turnstile_enable',     'value' => '0'],
            ['key' => 'turnstile_site_key',   'value' => ''],
            ['key' => 'turnstile_secret_key', 'value' => '']
        ];

        $this->db->table('app_config')->ignore(true)->insertBatch($turnstile_values);
    }

    /**
     * Revert a migration step.
     */
    public function down(): void
    {
        $turnstile_keys = [
            'turnstile_enable',
            'turnstile_site_key',
            'turnstile_secret_key'
        ];

        $this->db->table('app_config')->whereIn('key', $turnstile_keys)->delete();
    }
}

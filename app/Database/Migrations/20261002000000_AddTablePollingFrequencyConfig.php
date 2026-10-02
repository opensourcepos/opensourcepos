<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTablePollingFrequencyConfig extends Migration
{
    public function up(): void
    {
        $this->db->table('app_config')->ignore(true)->insert([
            'key'   => 'table_polling_frequency',
            'value' => '0',
        ]);
    }

    public function down(): void
    {
        $this->db->table('app_config')->where('key', 'table_polling_frequency')->delete();
    }
}

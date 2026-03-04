<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_add_ubl_config extends Migration
{
    public function up(): void
    {
        log_message('info', 'Adding UBL configuration.');
        
        $config_values = [
            ['key' => 'invoice_format', 'value' => 'pdf_only']
        ];
        
        $this->db->table('app_config')->ignore(true)->insertBatch($config_values);
    }

    public function down(): void
    {
        $this->db->table('app_config')->whereIn('key', ['invoice_format'])->delete();
    }
}
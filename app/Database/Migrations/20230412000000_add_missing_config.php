<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_add_missing_config extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        $image_values = [
            ['key' => 'account_number',                    'value' => ''],  // This has no current maintenance, but it's used in Sales
            ['key' => 'category_dropdown',                 'value' => ''],
            ['key' => 'smtp_host',                         'value' => ''],
            ['key' => 'smtp_user',                         'value' => ''],
            ['key' => 'smtp_pass',                         'value' => ''],
            ['key' => 'login_form',                        'value' => ''],
            ['key' => 'receiving_calculate_average_price', 'value' => ''],
            ['key' => 'payment_message',                   'value' => '']
        ];

        $this->db->table('app_config')->ignore(true)->insertBatch($image_values);
    }

    /**
     * Revert a migration step.
     */
    public function down(): void
    {
        // No need to remove necessary config values.
    }
}

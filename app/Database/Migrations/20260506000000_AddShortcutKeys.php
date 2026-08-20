<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddShortcutKeys extends Migration
{
    public function up(): void
    {
        $shortcutValues = [
            ['key' => 'key_cancel', 'value' => '27 | ESC'],
            ['key' => 'key_items', 'value' => '49 | ALT + 1'],
            ['key' => 'key_customers', 'value' => '50 | ALT + 2'],
            ['key' => 'key_suspend', 'value' => '51 | ALT + 3'],
            ['key' => 'key_suspended', 'value' => '52 | ALT + 4'],
            ['key' => 'key_amount', 'value' => '53 | ALT + 5'],
            ['key' => 'key_payment', 'value' => '54 | ALT + 6'],
            ['key' => 'key_complete', 'value' => '55 | ALT + 7'],
            ['key' => 'key_finish', 'value' => '56 | ALT + 8'],
            ['key' => 'key_help', 'value' => '57 | ALT + 9'],
        ];

        $this->db->table('app_config')->ignore(true)->insertBatch($shortcutValues);
    }

    public function down(): void
    {
        $shortcutKeys = [
            'key_cancel',
            'key_items',
            'key_customers',
            'key_suspend',
            'key_suspended',
            'key_amount',
            'key_payment',
            'key_complete',
            'key_finish',
            'key_help',
        ];

        $this->db->table('app_config')
            ->whereIn('key', $shortcutKeys)
            ->delete();
    }
}

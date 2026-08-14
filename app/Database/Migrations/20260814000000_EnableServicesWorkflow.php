<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Enables the services workflow (non-stock items, work orders, quotes and invoices).
 *
 * Work orders, quotes and invoices already work in the register, but the config keys
 * that turn them on were only ever seeded by older upgrade scripts. Installations that
 * start from the initial schema never got them, so the modes were silently unavailable.
 * This migration inserts any missing keys and turns Work Order support on.
 */
class EnableServicesWorkflow extends Migration
{
    public function up(): void
    {
        $this->db->table('app_config')->ignore(true)->insertBatch([
            ['key' => 'default_register_mode', 'value' => 'sale'],
            ['key' => 'invoice_type', 'value' => 'invoice'],
            ['key' => 'last_used_invoice_number', 'value' => '0'],
            ['key' => 'last_used_quote_number', 'value' => '0'],
            ['key' => 'last_used_work_order_number', 'value' => '0'],
            ['key' => 'line_sequence', 'value' => '0'],
            ['key' => 'quote_default_comments', 'value' => 'This is a default quote comment'],
            ['key' => 'sales_quote_format', 'value' => 'Q%y{QSEQ:6}'],
            ['key' => 'work_order_format', 'value' => 'W%y{WSEQ:6}'],
            ['key' => 'work_order_enable', 'value' => '1'],
        ]);

        $this->db->table('app_config')
            ->where('key', 'work_order_enable')
            ->update(['value' => '1']);
    }

    public function down(): void
    {
    }
}

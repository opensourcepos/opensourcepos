<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterSecondaryCurrencyRatePrecision extends Migration
{
    public function up(): void
    {
        $tables = ['sales', 'receivings', 'expenses'];

        foreach ($tables as $table) {
            $this->db->query(
                'ALTER TABLE ' . $this->db->prefixTable($table) .
                ' MODIFY `secondary_currency_rate` DECIMAL(15,0) NULL DEFAULT NULL'
            );
        }
    }

    public function down(): void
    {
        $tables = ['sales', 'receivings', 'expenses'];

        foreach ($tables as $table) {
            $this->db->query(
                'ALTER TABLE ' . $this->db->prefixTable($table) .
                ' MODIFY `secondary_currency_rate` DECIMAL(15,6) NULL DEFAULT NULL'
            );
        }
    }
}

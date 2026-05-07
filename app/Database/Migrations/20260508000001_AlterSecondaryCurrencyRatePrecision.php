<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Database;

class AlterSecondaryCurrencyRatePrecision extends Migration
{
    public function up(): void
    {
        $forge = Database::forge();
        $fields = [
            'secondary_currency_rate' => [
                'type' => 'DECIMAL',
                'constraint' => '15,6',
                'null' => true,
                'default' => null,
            ],
        ];

        foreach (['sales', 'receivings', 'expenses'] as $table) {
            $forge->modifyColumn($table, $fields);
        }
    }

    public function down(): void
    {
        $forge = Database::forge();
        $fields = [
            'secondary_currency_rate' => [
                'type' => 'DECIMAL',
                'constraint' => '15,0',
                'null' => true,
                'default' => null,
            ],
        ];

        foreach (['sales', 'receivings', 'expenses'] as $table) {
            $forge->modifyColumn($table, $fields);
        }
    }
}

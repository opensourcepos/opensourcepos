<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSecondaryCurrencyRateToTransactionHeaders extends Migration
{
    public function up(): void
    {
        $columns = [
            'secondary_currency_rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,6',
                'null'       => true,
                'default'    => null
            ]
        ];

        $this->forge->addColumn('sales', $columns);
        $this->forge->addColumn('receivings', $columns);
        $this->forge->addColumn('expenses', $columns);
    }

    public function down(): void
    {
        $this->forge->dropColumn('sales', 'secondary_currency_rate');
        $this->forge->dropColumn('receivings', 'secondary_currency_rate');
        $this->forge->dropColumn('expenses', 'secondary_currency_rate');
    }
}

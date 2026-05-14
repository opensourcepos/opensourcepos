<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_PaymentTransactions extends Migration
{
    public function up(): void
    {
        $forge = \Config\Services::forge();
        
        $forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'provider_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false
            ],
            'sale_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true
            ],
            'transaction_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false
            ],
            'amount' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'null' => false
            ],
            'currency' => [
                'type' => 'VARCHAR',
                'constraint' => 3,
                'default' => 'USD',
                'null' => false
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'authorized', 'completed', 'failed', 'refunded', 'cancelled'],
                'default' => 'pending',
                'null' => false
            ],
            'metadata' => [
                'type' => 'JSON',
                'null' => true
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true
            ]
        ]);
        
        $forge->addKey('id', true);
        $forge->addKey('provider_id');
        $forge->addKey('sale_id');
        $forge->addKey('transaction_id');
        $forge->addKey('status');
        
        $forge->createTable('payment_transactions', true);
    }

    public function down(): void
    {
        $forge = \Config\Services::forge();
        $forge->dropTable('payment_transactions', true);
    }
}
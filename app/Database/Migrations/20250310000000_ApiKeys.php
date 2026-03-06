<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ApiKeys extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'api_key_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'employee_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'key_hash' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'comment' => 'SHA-256 hash of the API key'
            ],
            'key_prefix' => [
                'type' => 'VARCHAR',
                'constraint' => 12,
                'comment' => 'First 12 chars for UI identification'
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'User-friendly key name'
            ],
            'last_used' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'created' => [
                'type' => 'DATETIME',
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Optional key expiration'
            ],
            'disabled' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0
            ]
        ]);
        
        $this->forge->addKey('api_key_id', true);
        $this->forge->addKey('employee_id');
        $this->forge->addKey('key_hash');
        $this->forge->addForeignKey('employee_id', 'employees', 'person_id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('api_keys', true);
        
        $seeder = \Config\Database::seeder();
        $seeder->call('ApiKeysSeeder');
    }

    public function down(): void
    {
        $this->forge->dropTable('api_keys', true);
    }
}
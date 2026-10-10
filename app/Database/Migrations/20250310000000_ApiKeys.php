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
                'constraint' => 10
            ],
            'key_hash' => [
                'type' => 'VARCHAR',
                'constraint' => 64
            ],
            'key_prefix' => [
                'type' => 'VARCHAR',
                'constraint' => 12
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'last_used' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'created' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true
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
        
        $this->forge->createTable('api_keys', true);
        
        $this->db->query(
            'ALTER TABLE ' . $this->db->prefixTable('api_keys') . 
            ' ADD CONSTRAINT ' . $this->db->prefixTable('api_keys') . '_employee_id_foreign' .
            ' FOREIGN KEY (employee_id) REFERENCES ' . $this->db->prefixTable('employees') . 
            ' (person_id) ON DELETE CASCADE ON UPDATE CASCADE'
        );
        
        $this->db->query(
            'INSERT INTO ' . $this->db->prefixTable('permissions') . ' (permission_id, module_id)' .
            " VALUES ('api_keys', 'office') ON DUPLICATE KEY UPDATE permission_id = 'api_keys'"
        );
        
        $this->db->query(
            'INSERT INTO ' . $this->db->prefixTable('modules') . ' (module_id, name_lang_key, desc_lang_key, sort)' .
            " VALUES ('api_keys', 'module_api_keys', 'module_desc_api_keys', 25)" .
            " ON DUPLICATE KEY UPDATE module_id = 'module_id'"
        );
    }

    public function down(): void
    {
        $this->db->query(
            'ALTER TABLE ' . $this->db->prefixTable('api_keys') . 
            ' DROP FOREIGN KEY ' . $this->db->prefixTable('api_keys') . '_employee_id_foreign'
        );
        
        $this->db->query(
            'DELETE FROM ' . $this->db->prefixTable('permissions') . " WHERE permission_id = 'api_keys'"
        );
        
        $this->db->query(
            'DELETE FROM ' . $this->db->prefixTable('modules') . " WHERE module_id = 'api_keys'"
        );
        
        $this->forge->dropTable('api_keys', true);
    }
}
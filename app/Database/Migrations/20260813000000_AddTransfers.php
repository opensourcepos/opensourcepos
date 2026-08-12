<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTransfers extends Migration
{
    public function up(): void
    {
        $this->db->query('CREATE TABLE IF NOT EXISTS ' . $this->db->prefixTable('transfers') . ' (
            `transfer_id` int(11) NOT NULL AUTO_INCREMENT,
            `transfer_time` datetime NOT NULL,
            `employee_id` int(11) NOT NULL,
            `location_from` int(11) NOT NULL,
            `location_to` int(11) NOT NULL,
            `comment` text,
            PRIMARY KEY (`transfer_id`),
            INDEX (`transfer_time`),
            INDEX (`location_from`),
            INDEX (`location_to`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->db->query('CREATE TABLE IF NOT EXISTS ' . $this->db->prefixTable('transfers_items') . ' (
            `transfer_id` int(11) NOT NULL,
            `item_id` int(11) NOT NULL,
            `line` int(3) NOT NULL,
            `quantity` decimal(15,3) NOT NULL DEFAULT 0,
            `item_location` int(11) NOT NULL,
            `description` varchar(255) NOT NULL DEFAULT \'\',
            PRIMARY KEY (`transfer_id`, `item_id`, `line`),
            INDEX (`item_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        // Register the module, permission and admin grant for the transfers module
        $this->db->table('modules')->insert([
            'name_lang_key' => 'module_transfers',
            'desc_lang_key' => 'module_transfers_desc',
            'sort'          => 65,
            'module_id'     => 'transfers',
        ]);

        $this->db->table('permissions')->insert([
            'permission_id' => 'transfers',
            'module_id'     => 'transfers',
        ]);

        $this->db->table('grants')->insert([
            'permission_id' => 'transfers',
            'person_id'     => 1,
            'menu_group'    => 'home',
        ]);

        // Report permission so the detailed transfers report shows in the reports listing
        $this->db->table('permissions')->insert([
            'permission_id' => 'reports_transfers',
            'module_id'     => 'reports',
        ]);

        $this->db->table('grants')->insert([
            'permission_id' => 'reports_transfers',
            'person_id'     => 1,
            'menu_group'    => '--',
        ]);
    }

    public function down(): void
    {
        $this->db->table('grants')->where('permission_id', 'reports_transfers')->delete();
        $this->db->table('permissions')->where('permission_id', 'reports_transfers')->delete();
        $this->db->table('grants')->where('permission_id', 'transfers')->delete();
        $this->db->table('permissions')->where('permission_id', 'transfers')->delete();
        $this->db->table('modules')->where('module_id', 'transfers')->delete();
        $this->db->query('DROP TABLE IF EXISTS ' . $this->db->prefixTable('transfers_items'));
        $this->db->query('DROP TABLE IF EXISTS ' . $this->db->prefixTable('transfers'));
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRequisitions extends Migration
{
    public function up(): void
    {
        $this->db->query('CREATE TABLE IF NOT EXISTS ' . $this->db->prefixTable('requisitions') . ' (
            `requisition_id` int(11) NOT NULL AUTO_INCREMENT,
            `requisition_time` datetime NOT NULL,
            `requested_by` int(11) NOT NULL,
            `location_from` int(11) NOT NULL,
            `location_to` int(11) NOT NULL,
            `status` tinyint(2) NOT NULL DEFAULT 0,
            `comment` text,
            `approved_by` int(11) DEFAULT NULL,
            `approved_time` datetime DEFAULT NULL,
            PRIMARY KEY (`requisition_id`),
            INDEX (`requisition_time`),
            INDEX (`location_from`),
            INDEX (`location_to`),
            INDEX (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->db->query('CREATE TABLE IF NOT EXISTS ' . $this->db->prefixTable('requisition_items') . ' (
            `requisition_id` int(11) NOT NULL,
            `item_id` int(11) NOT NULL,
            `line` int(3) NOT NULL,
            `quantity` decimal(15,3) NOT NULL DEFAULT 0,
            `item_location` int(11) NOT NULL,
            `description` varchar(255) NOT NULL DEFAULT \'\',
            PRIMARY KEY (`requisition_id`, `item_id`, `line`),
            INDEX (`item_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        // Register the module, permission and admin grant for the requisitions module
        $this->db->table('modules')->insert([
            'name_lang_key' => 'module_requisitions',
            'desc_lang_key' => 'module_requisitions_desc',
            'sort'          => 66,
            'module_id'     => 'requisitions',
        ]);

        $this->db->table('permissions')->insert([
            'permission_id' => 'requisitions',
            'module_id'     => 'requisitions',
        ]);

        $this->db->table('grants')->insert([
            'permission_id' => 'requisitions',
            'person_id'     => 1,
            'menu_group'    => 'home',
        ]);

        // Grant per-location permissions for existing stock locations
        $locations = $this->db->table('stock_locations')->where('deleted', 0)->get()->getResultArray();
        $employees = $this->db->table('people')
            ->join('employees', 'employees.person_id = people.person_id')
            ->select('people.person_id')
            ->get()
            ->getResultArray();

        foreach ($locations as $location) {
            $permission_id = 'requisitions_' . str_replace(' ', '_', $location['location_name']);

            if ($this->db->table('permissions')->where('permission_id', $permission_id)->countAllResults() > 0) {
                continue;
            }

            $this->db->table('permissions')->insert([
                'permission_id' => $permission_id,
                'module_id'     => 'requisitions',
                'location_id'   => $location['location_id'],
            ]);

            foreach ($employees as $employee) {
                $this->db->table('grants')->insert([
                    'permission_id' => $permission_id,
                    'person_id'     => $employee['person_id'],
                    'menu_group'    => '--',
                ]);
            }
        }
    }

    public function down(): void
    {
        $this->db->table('grants')->like('permission_id', 'requisitions', 'after')->delete();
        $this->db->table('permissions')->like('permission_id', 'requisitions', 'after')->delete();
        $this->db->table('modules')->where('module_id', 'requisitions')->delete();
        $this->db->query('DROP TABLE IF EXISTS ' . $this->db->prefixTable('requisition_items'));
        $this->db->query('DROP TABLE IF EXISTS ' . $this->db->prefixTable('requisitions'));
    }
}

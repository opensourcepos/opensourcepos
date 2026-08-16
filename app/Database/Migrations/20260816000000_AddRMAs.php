<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRMAs extends Migration
{
    public function up(): void
    {
        $this->db->query('CREATE TABLE IF NOT EXISTS ' . $this->db->prefixTable('rmas') . ' (
            `rma_id` int(11) NOT NULL AUTO_INCREMENT,
            `rma_time` datetime NOT NULL,
            `employee_id` int(11) NOT NULL,
            `rma_type` tinyint(2) NOT NULL DEFAULT 0,
            `location_id` int(11) NOT NULL,
            `supplier_id` int(11) DEFAULT NULL,
            `customer_id` int(11) DEFAULT NULL,
            `sale_id` int(11) DEFAULT NULL,
            `resolution` varchar(20) DEFAULT NULL,
            `resolved_by` int(11) DEFAULT NULL,
            `resolved_time` datetime DEFAULT NULL,
            `comment` text,
            PRIMARY KEY (`rma_id`),
            INDEX (`rma_time`),
            INDEX (`location_id`),
            INDEX (`resolution`),
            INDEX (`rma_type`),
            INDEX (`sale_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->db->query('CREATE TABLE IF NOT EXISTS ' . $this->db->prefixTable('rma_items') . ' (
            `rma_id` int(11) NOT NULL,
            `item_id` int(11) NOT NULL,
            `line` int(3) NOT NULL,
            `quantity` decimal(15,3) NOT NULL DEFAULT 0,
            `item_location` int(11) NOT NULL,
            `description` varchar(255) NOT NULL DEFAULT \'\',
            PRIMARY KEY (`rma_id`, `item_id`, `line`),
            INDEX (`item_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        // Register the module, permission and admin grant for the rmas module
        $this->db->table('modules')->insert([
            'name_lang_key' => 'module_rmas',
            'desc_lang_key' => 'module_rmas_desc',
            'sort'          => 67,
            'module_id'     => 'rmas',
        ]);

        $this->db->table('permissions')->insert([
            'permission_id' => 'rmas',
            'module_id'     => 'rmas',
        ]);

        $this->db->table('grants')->insert([
            'permission_id' => 'rmas',
            'person_id'     => 1,
            'menu_group'    => 'home',
        ]);

        // Report permission so the detailed RMA report shows in the reports listing
        $this->db->table('permissions')->insert([
            'permission_id' => 'reports_rmas',
            'module_id'     => 'reports',
        ]);

        $this->db->table('grants')->insert([
            'permission_id' => 'reports_rmas',
            'person_id'     => 1,
            'menu_group'    => '--',
        ]);

        // Grant per-location permissions for existing stock locations
        $locations = $this->db->table('stock_locations')->where('deleted', 0)->get()->getResultArray();
        $employees = $this->db->table('people')
            ->join('employees', 'employees.person_id = people.person_id')
            ->select('people.person_id')
            ->get()
            ->getResultArray();

        foreach ($locations as $location) {
            $permission_id = 'rmas_' . str_replace(' ', '_', $location['location_name']);

            if ($this->db->table('permissions')->where('permission_id', $permission_id)->countAllResults() > 0) {
                continue;
            }

            $this->db->table('permissions')->insert([
                'permission_id' => $permission_id,
                'module_id'     => 'rmas',
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
        $this->db->table('grants')->like('permission_id', 'rmas', 'after')->delete();
        $this->db->table('permissions')->like('permission_id', 'rmas', 'after')->delete();
        $this->db->table('grants')->like('permission_id', 'reports_rmas', 'after')->delete();
        $this->db->table('permissions')->like('permission_id', 'reports_rmas', 'after')->delete();
        $this->db->table('modules')->where('module_id', 'rmas')->delete();
        $this->db->query('DROP TABLE IF EXISTS ' . $this->db->prefixTable('rma_items'));
        $this->db->query('DROP TABLE IF EXISTS ' . $this->db->prefixTable('rmas'));
    }
}

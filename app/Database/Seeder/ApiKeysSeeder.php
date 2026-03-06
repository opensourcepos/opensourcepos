<?php

namespace App\Database\Seeder;

use CodeIgniter\Database\Seeder;

class ApiKeysSeeder extends Seeder
{
    public function run(): void
    {
        $this->db->query("
            INSERT INTO `ospos_permissions` (`permission_id`, `module_id`) 
            VALUES ('api_keys', 'office')
            ON DUPLICATE KEY UPDATE `permission_id` = 'api_keys'
        ");
        
        $this->db->query("
            INSERT INTO `ospos_modules` (`module_id`, `name_lang_key`, `desc_lang_key`, `sort`) 
            VALUES ('api_keys', 'module_api_keys', 'module_desc_api_keys', 25)
            ON DUPLICATE KEY UPDATE `module_id` = 'module_id'
        ");
    }
}
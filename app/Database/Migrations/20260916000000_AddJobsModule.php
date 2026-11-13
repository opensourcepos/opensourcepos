<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddJobsModule extends Migration
{
    public function up(): void
    {
        $this->db->table('modules')->insert([
            'module_id'     => 'jobs',
            'name_lang_key' => 'jobs',
            'desc_lang_key' => 'jobs_desc',
            'sort'          => 130,
        ]);

        $this->db->table('permissions')->insert([
            'permission_id' => 'jobs',
            'module_id'     => 'jobs',
        ]);

        $this->db->table('grants')->insert([
            'permission_id' => 'jobs',
            'person_id'     => 1,
            'menu_group'    => 'office',
        ]);
    }

    public function down(): void
    {
        $this->db->table('grants')->where('permission_id', 'jobs')->delete();
        $this->db->table('permissions')->where('permission_id', 'jobs')->delete();
        $this->db->table('modules')->where('module_id', 'jobs')->delete();
    }
}

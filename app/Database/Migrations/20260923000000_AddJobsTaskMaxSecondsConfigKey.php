<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddJobsTaskMaxSecondsConfigKey extends Migration
{
    public function up(): void
    {
        $this->db->table('app_config')->ignore(true)->insert(['key' => 'jobs_task_max_seconds', 'value' => '30']);
    }

    public function down(): void
    {
        $this->db->table('app_config')->where('key', 'jobs_task_max_seconds')->delete();
    }
}

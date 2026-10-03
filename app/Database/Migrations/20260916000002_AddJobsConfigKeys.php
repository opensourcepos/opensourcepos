<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddJobsConfigKeys extends Migration
{
    public function up(): void
    {
        $jobsConfigValues = [
            ['key' => 'jobs_mode', 'value' => 'web'],
            ['key' => 'jobs_web_max_seconds', 'value' => '5'],
        ];

        $this->db->table('app_config')->ignore(true)->insertBatch($jobsConfigValues);
    }

    public function down(): void
    {
        $jobsConfigKeys = [
            'jobs_mode',
            'jobs_web_max_seconds',
        ];

        $this->db->table('app_config')
            ->whereIn('key', $jobsConfigKeys)
            ->delete();
    }
}

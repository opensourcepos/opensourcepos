<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJobThrottles extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'throttle_id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'max_count'   => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'period'      => ['type' => 'ENUM', 'constraint' => ['minute', 'hour', 'day', 'month']],
            'deleted'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
        ]);
        $this->forge->addPrimaryKey('throttle_id');
        $this->forge->createTable('job_throttles');
    }

    public function down(): void
    {
        $this->forge->dropTable('job_throttles');
    }
}

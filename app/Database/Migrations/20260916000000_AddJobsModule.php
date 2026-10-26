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

        $this->forge->addField([
            'throttle_id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'max_count'   => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'period'      => ['type' => 'ENUM', 'constraint' => ['second', 'minute', 'hour', 'day', 'month']],
            'deleted'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
        ]);
        $this->forge->addPrimaryKey('throttle_id');
        $this->forge->createTable('job_throttles');

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'batch_id'   => ['type' => 'VARCHAR', 'constraint' => 36],
            'context'    => ['type' => 'TEXT'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('batch_id');
        $this->forge->createTable('import_batch_contexts');

        $this->forge->addField([
            'id'           => ['type' => 'bigint', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'queue'        => ['type' => 'varchar', 'constraint' => 64, 'null' => false],
            'batch_id'     => ['type' => 'varchar', 'constraint' => 36, 'null' => true],
            'context_id'   => ['type' => 'int', 'unsigned' => true, 'null' => true],
            'payload'      => ['type' => 'text', 'null' => false],
            'priority'     => ['type' => 'varchar', 'constraint' => 64, 'null' => false, 'default' => 'default'],
            'status'       => ['type' => 'tinyint', 'unsigned' => true, 'null' => false, 'default' => 0],
            'attempts'     => ['type' => 'tinyint', 'unsigned' => true, 'null' => false, 'default' => 0],
            'available_at' => ['type' => 'int', 'unsigned' => true, 'null' => false],
            'created_at'   => ['type' => 'int', 'unsigned' => true, 'null' => false],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['queue', 'priority', 'status', 'available_at'], false, false, 'queue_priority_status_available_at');
        $this->forge->addKey('batch_id', false, false, 'queue_jobs_batch_id');
        $this->forge->addForeignKey('context_id', 'import_batch_contexts', 'id', '', 'RESTRICT', 'queue_jobs_context_id_fk');
        $this->forge->createTable('queue_jobs', true);

        $this->forge->addField([
            'id'         => ['type' => 'bigint', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'connection' => ['type' => 'varchar', 'constraint' => 64, 'null' => false],
            'queue'      => ['type' => 'varchar', 'constraint' => 64, 'null' => false],
            'batch_id'   => ['type' => 'varchar', 'constraint' => 36, 'null' => true],
            'context_id' => ['type' => 'int', 'unsigned' => true, 'null' => true],
            'payload'    => ['type' => 'text', 'null' => false],
            'priority'   => ['type' => 'varchar', 'constraint' => 64, 'null' => false, 'default' => 'default'],
            'attempts'   => ['type' => 'tinyint', 'unsigned' => true, 'null' => false, 'default' => 0],
            'exception'  => ['type' => 'text', 'null' => false],
            'failed_at'  => ['type' => 'int', 'unsigned' => true, 'null' => false],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('queue');
        $this->forge->addKey('batch_id', false, false, 'queue_jobs_failed_batch_id');
        $this->forge->addForeignKey('context_id', 'import_batch_contexts', 'id', '', 'RESTRICT', 'queue_jobs_failed_context_id_fk');
        $this->forge->createTable('queue_jobs_failed', true);

        $this->forge->addField([
            'id'           => ['type' => 'BIGINT', 'constraint' => 11, 'unsigned' => true],
            'queue'        => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => false],
            'batch_id'     => ['type' => 'VARCHAR', 'constraint' => 36, 'null' => true],
            'context_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'payload'      => ['type' => 'TEXT', 'null' => false],
            'priority'     => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => false, 'default' => 'default'],
            'attempts'     => ['type' => 'TINYINT', 'unsigned' => true, 'null' => false, 'default' => 0],
            'available_at' => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'created_at'   => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'paused_at'    => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'paused_by'    => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => false],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('queue');
        $this->forge->addKey('batch_id', false, false, 'queue_paused_jobs_batch_id');
        $this->forge->addForeignKey('context_id', 'import_batch_contexts', 'id', '', 'RESTRICT', 'queue_paused_jobs_context_id_fk');
        $this->forge->createTable('queue_paused_jobs', true);

        $this->forge->addField([
            'id'         => ['type' => 'VARCHAR', 'constraint' => 36],
            'type'       => ['type' => 'VARCHAR', 'constraint' => 64],
            'total'      => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'completed'  => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'failed'     => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'status'     => ['type' => 'ENUM', 'constraint' => ['pending', 'processing', 'completed', 'partial']],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['type', 'status']);
        $this->forge->createTable('import_batches');

        $jobsConfigValues = [
            ['key' => 'jobs_mode', 'value' => 'web'],
            ['key' => 'jobs_web_max_seconds', 'value' => '5'],
            ['key' => 'jobs_task_max_seconds', 'value' => '30'],
            ['key' => 'jobs_failed_retention_days', 'value' => '30'],
            ['key' => 'jobs_retention_days', 'value' => '7'],
            ['key' => 'jobs_auto_purge', 'value' => '1'],
            ['key' => 'jobs_retry_limit', 'value' => '3'],
            ['key' => 'jobs_manual_max_seconds', 'value' => '30'],
        ];

        $this->db->table('app_config')->ignore(true)->insertBatch($jobsConfigValues);
    }

    public function down(): void
    {
        $jobsConfigKeys = [
            'jobs_mode',
            'jobs_web_max_seconds',
            'jobs_task_max_seconds',
            'jobs_failed_retention_days',
            'jobs_retention_days',
            'jobs_auto_purge',
            'jobs_retry_limit',
            'jobs_manual_max_seconds',
        ];

        $this->db->table('app_config')->whereIn('key', $jobsConfigKeys)->delete();

        $this->forge->dropTable('import_batches', true);
        $this->forge->dropTable('queue_paused_jobs', true);
        $this->forge->dropTable('queue_jobs_failed', true);
        $this->forge->dropTable('queue_jobs', true);
        $this->forge->dropTable('import_batch_contexts', true);
        $this->forge->dropTable('job_throttles', true);

        $this->db->table('grants')->where('permission_id', 'jobs')->delete();
        $this->db->table('permissions')->where('permission_id', 'jobs')->delete();
        $this->db->table('modules')->where('module_id', 'jobs')->delete();
    }
}

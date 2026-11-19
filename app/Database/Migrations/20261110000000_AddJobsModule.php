<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddJobsModule extends Migration
{
    private const VENDOR_MIGRATIONS = [
        'CodeIgniter\Queue\Database\Migrations\AddQueueTables',
        'CodeIgniter\Queue\Database\Migrations\AddPriorityField',
        'CodeIgniter\Queue\Database\Migrations\ChangePayloadFieldTypeInSqlsrv',
    ];

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
            'queue'       => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true, 'default' => null],
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

        $queueJobsColumns = [
            'priority'   => ['type' => 'varchar', 'constraint' => 64, 'null' => false, 'default' => 'default', 'after' => 'payload'],
            'batch_id'   => ['type' => 'varchar', 'constraint' => 36, 'null' => true],
            'context_id' => ['type' => 'int', 'unsigned' => true, 'null' => true],
            'release_epoch' => ['type' => 'int', 'unsigned' => true, 'null' => false, 'default' => 0],
        ];
        $queueJobsFailedColumns = [
            'priority'   => $queueJobsColumns['priority'],
            'attempts'   => ['type' => 'tinyint', 'unsigned' => true, 'null' => false, 'default' => 0, 'after' => 'priority'],
            'batch_id'   => $queueJobsColumns['batch_id'],
            'context_id' => $queueJobsColumns['context_id'],
        ];

        if ($this->db->tableExists('queue_jobs')) {
            $this->upgradeQueueTable('queue_jobs', $queueJobsColumns, [
                'queue_priority_status_available_at' => ['queue', 'priority', 'status', 'available_at'],
                'queue_jobs_batch_id'                => 'batch_id',
            ], 'queue_jobs_context_id_fk');
        } else {
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
                'release_epoch' => ['type' => 'int', 'unsigned' => true, 'null' => false, 'default' => 0],
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addKey(['queue', 'priority', 'status', 'available_at'], false, false, 'queue_priority_status_available_at');
            $this->forge->addKey('batch_id', false, false, 'queue_jobs_batch_id');
            $this->forge->addForeignKey('context_id', 'import_batch_contexts', 'id', '', 'RESTRICT', 'queue_jobs_context_id_fk');
            $this->forge->createTable('queue_jobs');
        }

        if ($this->db->tableExists('queue_jobs_failed')) {
            $this->upgradeQueueTable('queue_jobs_failed', $queueJobsFailedColumns, [
                'queue'                      => 'queue',
                'queue_jobs_failed_batch_id' => 'batch_id',
            ], 'queue_jobs_failed_context_id_fk');
        } else {
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
            $this->forge->createTable('queue_jobs_failed');
        }

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
            'release_epoch' => ['type' => 'INT', 'unsigned' => true, 'null' => false, 'default' => 0],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('queue');
        $this->forge->addKey('batch_id', false, false, 'queue_jobs_paused_batch_id');
        $this->forge->addForeignKey('context_id', 'import_batch_contexts', 'id', '', 'RESTRICT', 'queue_jobs_paused_context_id_fk');
        $this->forge->createTable('queue_jobs_paused', true);

        if (!$this->db->fieldExists('release_epoch', 'queue_jobs_paused')) {
            $this->forge->addColumn('queue_jobs_paused', ['release_epoch' => $queueJobsColumns['release_epoch']]);
        }

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
            ['key' => 'table_polling_frequency', 'value' => '0'],
        ];

        $this->db->table('app_config')->ignore(true)->insertBatch($jobsConfigValues);

        // queue_jobs and queue_jobs_failed are created or upgraded above, so the
        // codeigniter4/queue vendor migrations must never run. Record them as
        // already applied so `spark migrate --all` skips them. They go into the
        // newest existing batch, which is either this migration's own batch (when
        // earlier migrations of the same run already wrote it) or an earlier one.
        // down() only detaches what this migration added to the queue tables, so
        // a rollback of this batch leaves a consistent vendor state either way.
        $lastBatch = (int)($this->db->table('migrations')->selectMax('batch')->get()->getRowArray()['batch'] ?? 0);
        $batch = max(1, $lastBatch);
        $now   = time();
        $vendorRows = [
            [
                'version'   => '2023-10-12-112040',
                'class'     => 'CodeIgniter\Queue\Database\Migrations\AddQueueTables',
                'group'     => 'default',
                'namespace' => 'CodeIgniter\Queue',
                'time'      => $now,
                'batch'     => $batch,
            ],
            [
                'version'   => '2023-11-05-064053',
                'class'     => 'CodeIgniter\Queue\Database\Migrations\AddPriorityField',
                'group'     => 'default',
                'namespace' => 'CodeIgniter\Queue',
                'time'      => $now,
                'batch'     => $batch,
            ],
            [
                'version'   => '2024-12-27-110712',
                'class'     => 'CodeIgniter\Queue\Database\Migrations\ChangePayloadFieldTypeInSqlsrv',
                'group'     => 'default',
                'namespace' => 'CodeIgniter\Queue',
                'time'      => $now,
                'batch'     => $batch,
            ],
        ];

        $recordedClasses = array_column(
            $this->db->table('migrations')->select('class')->whereIn('class', self::VENDOR_MIGRATIONS)->get()->getResultArray(),
            'class'
        );
        $missingRows = array_filter($vendorRows, static fn (array $row): bool => !in_array($row['class'], $recordedClasses, true));

        if ($missingRows !== []) {
            $this->db->table('migrations')->insertBatch(array_values($missingRows));
        }
    }

    private function upgradeQueueTable(string $table, array $columns, array $indexes, string $foreignKeyName): void
    {
        helper('migration');

        $missingColumns = array_filter($columns, fn(string $column): bool => !$this->db->fieldExists($column, $table), ARRAY_FILTER_USE_KEY);
        if ($missingColumns !== []) {
            $this->forge->addColumn($table, $missingColumns);
        }

        $hasPendingChanges = false;
        foreach ($indexes as $indexName => $fields) {
            if (!indexExists($table, $indexName, $this->db)) {
                $this->forge->addKey($fields, false, false, $indexName);
                $hasPendingChanges = true;
            }
        }

        if (!foreignKeyExists($foreignKeyName, $table)) {
            $this->forge->addForeignKey('context_id', 'import_batch_contexts', 'id', '', 'RESTRICT', $foreignKeyName);
            $hasPendingChanges = true;
        }

        if ($hasPendingChanges) {
            $this->forge->processIndexes($table);
        }
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
            'table_polling_frequency',
        ];

        $this->db->table('app_config')->whereIn('key', $jobsConfigKeys)->delete();

        $this->forge->dropTable('import_batches', true);
        $this->forge->dropTable('queue_jobs_paused', true);

        if ($this->vendorMigrationsRecorded()) {
            $this->detachFromQueueTable('queue_jobs', 'queue_jobs_context_id_fk', ['batch_id', 'context_id']);
            $this->detachFromQueueTable('queue_jobs_failed', 'queue_jobs_failed_context_id_fk', ['batch_id', 'context_id', 'attempts']);
        } else {
            $this->forge->dropTable('queue_jobs_failed', true);
            $this->forge->dropTable('queue_jobs', true);
        }

        $this->forge->dropTable('import_batch_contexts', true);
        $this->forge->dropTable('job_throttles', true);

        $this->db->table('grants')->where('permission_id', 'jobs')->delete();
        $this->db->table('permissions')->where('permission_id', 'jobs')->delete();
        $this->db->table('modules')->where('module_id', 'jobs')->delete();
    }

    /**
     * The vendor queue migrations own the queue tables. Whether they sit in
     * this migration's batch (their down() methods then run right after this
     * one, still expecting the tables) or in an earlier batch (they must stay
     * applied), this migration only removes what it added to those tables and
     * leaves the tables and the vendor history alone.
     */
    private function vendorMigrationsRecorded(): bool
    {
        return $this->db->table('migrations')
            ->whereIn('class', self::VENDOR_MIGRATIONS)
            ->countAllResults() > 0;
    }

    private function detachFromQueueTable(string $table, string $foreignKeyName, array $columns): void
    {
        helper('migration');

        if (foreignKeyExists($foreignKeyName, $table)) {
            $this->db->query('ALTER TABLE ' . $this->db->prefixTable($table) . ' DROP FOREIGN KEY ' . $this->db->escapeIdentifiers($foreignKeyName));
        }

        foreach ($columns as $column) {
            if ($this->db->fieldExists($column, $table)) {
                $this->forge->dropColumn($table, $column);
            }
        }
    }
}

<?php

namespace App\Libraries;

use CodeIgniter\Database\MigrationRunner;
use Config\Database;
use Exception;
use stdClass;

class MY_Migration extends MigrationRunner
{
    /**
     * @return bool
     */
    public function isLatest(): bool
    {
        $latestVersion = $this->getLatestMigration();
        $currentVersion = $this->getCurrentVersion();

        return $latestVersion === $currentVersion;
    }

    /**
     * @return int
     */
    public function getLatestMigration(): int
    {
        $migrations = $this->findMigrations();
        return (int) basename(end($migrations)->version);
    }

    /**
     * Gets the database version number
     *
     * @return int The version number of the last successfully run database migration.
     */
    public static function getCurrentVersion(): int
    {
        try {
            $db = Database::connect();
            if ($db->tableExists('migrations')) {
                $builder = $db->table('migrations');
                $builder->select('version')->orderBy('version', 'DESC')->limit(1);
                $result = $builder->get()->getRow();
                return $result ? (int) $result->version : 0;
            }
        } catch (Exception $e) {
            // Database not available yet (e.g. fresh install before schema).
            // Catches mysqli_sql_exception which is not a DatabaseException.
            return 0;
        }

        return 0;
    }

    /**
     * @return void
     */
    public function migrateToCI4(): void
    {
        $ci3_migrations_version = $this->ci3MigrationsExists();
        if ($ci3_migrations_version) {
            $this->migrateTable($ci3_migrations_version);
        }
    }

    /**
     * Checks to see if a ci3 version of the migrations table exists
     *
     * @return bool|string The version number of the last CI3 migration to run or false if the table is CI4 or doesn't exist
     */
    private function ci3MigrationsExists(): bool|string
    {
        try {
            if ($this->db->tableExists('migrations') && !$this->db->fieldExists('id', 'migrations')) {
                $builder = $this->db->table('migrations');
                $builder->select('version');
                $result = $builder->get()->getRow();
                return $result ? $result->version : false;
            }
        } catch (Exception $e) {
            // Database not available yet (e.g. fresh install before schema).
            // Catches mysqli_sql_exception which is not a DatabaseException.
        }

        return false;
    }

    /**
     * @param string $ci3_migrations_version
     * @return void
     */
    private function migrateTable(string $ci3_migrations_version): void
    {
        $this->convertTable();

        $available_migrations = $this->getAvailableMigrations();

        foreach ($available_migrations as $version => $path) {
            if ($version > (int)$ci3_migrations_version) {
                break;
            }

            $migration = new stdClass();
            $migration->version = $version;
            $migration->class = $path;
            $migration->namespace = 'App';

            $this->addHistory($migration, 1);
        }
    }

    /**
     * @return void
     */
    public function up(): void
    {
        // TODO: Implement up() method.
    }

    /**
     * @return void
     */
    public function down(): void
    {
        // TODO: Implement down() method.
    }

    /**
     * @return array
     */
    private function getAvailableMigrations(): array
    {
        $migrations = $this->findMigrations();
        $explodedMigrations = [];

        foreach ($migrations as $migration) {
            $version = substr($migration->uid, 0, 14);
            $path = substr($migration->uid, 14);

            $explodedMigrations[$version] = $path;
        }

        ksort($explodedMigrations);

        return $explodedMigrations;
    }

    /**
     * Converts the CI3 migrations database to CI4
     * @return void
     */
    public function convertTable(): void
    {
        $forge = Database::forge();
        $forge->dropTable('migrations');

        $this->ensureTable();
    }

}

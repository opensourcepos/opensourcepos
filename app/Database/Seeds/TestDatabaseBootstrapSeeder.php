<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Config\Database;

class TestDatabaseBootstrapSeeder extends Seeder
{
    public static function reset(): void
    {
        if (ENVIRONMENT !== 'testing') {
            throw new \RuntimeException('TestDatabaseBootstrapSeeder can only run in the testing environment.');
        }

        $config = config('Database');
        $group  = $config->tests;
        $dbName = $group['database'];

        if ($dbName === '' || !str_contains(strtolower($dbName), 'test')) {
            throw new \RuntimeException("Refusing to reset non-test database: {$dbName}");
        }

        $serverConn = Database::connect([
            'hostname' => $group['hostname'],
            'username' => $group['username'],
            'password' => $group['password'],
            'DBDriver' => $group['DBDriver'],
            'database' => null,
            'charset'  => $group['charset'] ?? 'utf8mb4',
            'DBCollat' => $group['DBCollat'] ?? 'utf8mb4_general_ci',
        ], false);

        $serverConn->query("DROP DATABASE IF EXISTS `{$dbName}`");
        $serverConn->query("CREATE DATABASE IF NOT EXISTS `{$dbName}`");
        $serverConn->close();

        // The application's shared 'tests' connection caches listTables()
        // results in dataCache['table_names'']. Because we dropped and
        // recreated the schema on a SEPARATE server connection above, that
        // cache is now stale. Reset it so subsequent listTables()/tableExists()
        // calls re-query the server instead of trusting a dropped schema's table
        // list (which previously caused "ospos_migrations doesn't exist" and
        // "column ... doesn't exist" errors for every class that followed a
        // bootstrap-reset class in the suite).
        $shared = Database::connect('tests');
        $shared->resetDataCache();
    }

    public function run(): void
    {
        self::reset();
    }
}

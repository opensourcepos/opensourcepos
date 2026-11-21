<?php

namespace Tests\Models;

use App\Models\Dinner_table;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;
use Tests\Support\DatabaseBootstrapTrait;

class Dinner_tableTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use DatabaseBootstrapTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $seedOnce    = true;
    protected $refresh     = false;
    protected $namespace   = null;

    protected function setUp(): void
    {
        $this->resetDatabaseOnce();

        parent::setUp();
    }

    public function testGetEmptyTables_ExcludesDeletedTablesWithStatusZero(): void
    {
        $db = Database::connect();
        $db->table('dinner_tables')->insert([
            'name'    => 'Deleted Empty Table',
            'status'  => 0,
            'deleted' => 1,
        ]);
        $deletedTableId = (int) $db->insertID();

        $db->table('dinner_tables')->insert([
            'name'    => 'Active Occupied Table',
            'status'  => 1,
            'deleted' => 0,
        ]);
        $currentTableId = (int) $db->insertID();

        $dinnerTableModel = model(Dinner_table::class);
        $emptyTables      = $dinnerTableModel->get_empty_tables($currentTableId);

        $this->assertArrayNotHasKey($deletedTableId, $emptyTables);
        $this->assertArrayHasKey($currentTableId, $emptyTables);
    }
}

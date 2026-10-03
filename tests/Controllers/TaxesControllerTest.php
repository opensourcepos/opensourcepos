<?php

namespace Tests\Controllers;

use CodeIgniter\Database\Config;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\EmployeeFixtureTrait;

class TaxesControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use EmployeeFixtureTrait;

    protected $migrate = true;
    protected $migrateOnce = true;
    protected $seedOnce = true;
    protected $refresh = false;
    protected $namespace = null;

    private static $doneBootstrap = false;

    protected function setUp(): void
    {
        if (self::$doneBootstrap === false) {
            Config::seeder($this->DBGroup)->call('App\Database\Seeds\TestDatabaseBootstrapSeeder');
            Config::connect($this->DBGroup)->close();

            self::$doneBootstrap = true;
        }

        parent::setUp();
    }

    protected function createTaxesEmployee(): int
    {
        $personId = $this->createEmployee();

        $db = Config::connect($this->DBGroup);
        $db->table('grants')->insert([
            'person_id'     => $personId,
            'permission_id' => 'taxes',
            'menu_group'    => 'office',
        ]);

        return $personId;
    }

    protected function loginAsTaxesEmployee(int $personId): void
    {
        $this->withSession([
            'person_id'  => $personId,
            'menu_group' => 'office',
        ]);
    }

    /**
     * Regression test: a negative tax rate must be
     * rejected by Taxes::postSave so it can never be persisted to tax_code_rate.
     */
    public function testPostSaveRejectsNegativeTaxRate(): void
    {
        $employeeId = $this->createTaxesEmployee();
        $this->loginAsTaxesEmployee($employeeId);

        $response = $this->post('/taxes/save', [
            'rate_tax_code_id'     => '1',
            'rate_tax_category_id' => '1',
            'rate_jurisdiction_id' => '1',
            'tax_rate'             => '-50',
            'tax_rounding_code'    => '0',
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }
}

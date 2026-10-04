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

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    protected function createTaxesEmployee(): int
    {
        return $this->createEmployee(
            grants: [
                ['permission_id' => 'taxes', 'menu_group' => 'office'],
            ]
        );
    }

    protected function loginAsTaxesEmployee(int $personId): void
    {
        $this->withSession([
            'person_id'  => $personId,
            'menu_group' => 'office',
        ]);
    }

    /**
     * Regression test: `tax_code_name[]` containing `<`/`>` (the stored-XSS
     * vector) must be rejected by postSave_tax_codes before anything is saved.
     */
    public function testPostSaveTaxCodesRejectsMaliciousName(): void
    {
        $employeeId = $this->createTaxesEmployee();
        $this->loginAsTaxesEmployee($employeeId);

        $response = $this->post('/taxes/save_tax_codes', [
            'tax_code_id'   => ['-1'],
            'tax_code'      => ['TC' . uniqid()],
            'tax_code_name' => ['<svg onload=alert(1)>'],
            'city'          => [''],
            'state'         => [''],
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    /**
     * Legitimate unicode tax code names must not be rejected by the XSS guard.
     */
    public function testPostSaveTaxCodesAcceptsUnicodeName(): void
    {
        $employeeId = $this->createTaxesEmployee();
        $this->loginAsTaxesEmployee($employeeId);

        $response = $this->post('/taxes/save_tax_codes', [
            'tax_code_id'   => ['-1'],
            'tax_code'      => ['TC' . uniqid()],
            'tax_code_name' => ["Impôt, incl."],
            'city'          => [''],
            'state'         => [''],
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);
    }

    /**
     * Regression test: `tax_category[]` containing `<`/`>` must be rejected.
     */
    public function testPostSaveTaxCategoriesRejectsMaliciousName(): void
    {
        $employeeId = $this->createTaxesEmployee();
        $this->loginAsTaxesEmployee($employeeId);

        $response = $this->post('/taxes/save_tax_categories', [
            'tax_category_id'    => ['-1'],
            'tax_category'       => ['<svg onload=alert(1)>'],
            'tax_group_sequence' => ['1'],
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    /**
     * Legitimate unicode tax category names must not be rejected.
     */
    public function testPostSaveTaxCategoriesAcceptsUnicodeName(): void
    {
        $employeeId = $this->createTaxesEmployee();
        $this->loginAsTaxesEmployee($employeeId);

        $response = $this->post('/taxes/save_tax_categories', [
            'tax_category_id'    => ['-1'],
            'tax_category'       => ["Impôt, incl."],
            'tax_group_sequence' => ['1'],
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);
    }

    /**
     * Regression test: `jurisdiction_name[]` containing `<`/`>` must be rejected.
     */
    public function testPostSaveTaxJurisdictionsRejectsMaliciousName(): void
    {
        $employeeId = $this->createTaxesEmployee();
        $this->loginAsTaxesEmployee($employeeId);

        $response = $this->post('/taxes/save_tax_jurisdictions', [
            'jurisdiction_id'     => ['-1'],
            'jurisdiction_name'   => ['<svg onload=alert(1)>'],
            'tax_group'           => ['1'],
            'tax_type'            => ['0'],
            'reporting_authority' => [''],
            'tax_group_sequence'  => ['1'],
            'cascade_sequence'    => ['1'],
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    /**
     * Legitimate unicode jurisdiction names must not be rejected.
     */
    public function testPostSaveTaxJurisdictionsAcceptsUnicodeName(): void
    {
        $employeeId = $this->createTaxesEmployee();
        $this->loginAsTaxesEmployee($employeeId);

        $response = $this->post('/taxes/save_tax_jurisdictions', [
            'jurisdiction_id'     => ['-1'],
            'jurisdiction_name'   => ["Impôt, incl."],
            'tax_group'           => ['1'],
            'tax_type'            => ['0'],
            'reporting_authority' => [''],
            'tax_group_sequence'  => ['1'],
            'cascade_sequence'    => ['1'],
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);
    }
}

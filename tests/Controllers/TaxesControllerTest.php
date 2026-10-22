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
    public function testPostSaveTaxCodes_RejectsMaliciousName(): void
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
    public function testPostSaveTaxCodes_AcceptsUnicodeName(): void
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
     * Slash-separated tax names (e.g. "GST/HST") are legitimate and must not be
     * rejected by the validation guard.
     */
    public function testPostSaveTaxCodes_AcceptsSlashName(): void
    {
        $employeeId = $this->createTaxesEmployee();
        $this->loginAsTaxesEmployee($employeeId);

        $response = $this->post('/taxes/save_tax_codes', [
            'tax_code_id'   => ['-1'],
            'tax_code'      => ['TC' . uniqid()],
            'tax_code_name' => ['GST/HST'],
            'city'          => [''],
            'state'         => [''],
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);
    }

    /**
     * Parenthesised tax names (e.g. "VAT (20%)") are legitimate and must not be
     * rejected by the validation guard.
     */
    public function testPostSaveTaxCodes_AcceptsParenthesesName(): void
    {
        $employeeId = $this->createTaxesEmployee();
        $this->loginAsTaxesEmployee($employeeId);

        $response = $this->post('/taxes/save_tax_codes', [
            'tax_code_id'   => ['-1'],
            'tax_code'      => ['TC' . uniqid()],
            'tax_code_name' => ['VAT (20%)'],
            'city'          => [''],
            'state'         => [''],
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);
    }

    /**
     * A tax code whose name is left blank is legitimate (the form does not
     * require a name) and must not be rejected by the validation guard.
     */
    public function testPostSaveTaxCodes_AcceptsBlankName(): void
    {
        $employeeId = $this->createTaxesEmployee();
        $this->loginAsTaxesEmployee($employeeId);

        $response = $this->post('/taxes/save_tax_codes', [
            'tax_code_id'   => ['-1'],
            'tax_code'      => ['TC' . uniqid()],
            'tax_code_name' => [''],
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
    public function testPostSaveTaxCategories_RejectsMaliciousName(): void
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
    public function testPostSaveTaxCategories_AcceptsUnicodeName(): void
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
     * CJK tax names (e.g. "消費税", the Japanese consumption tax) are legitimate
     * and must not be rejected by the validation guard.
     */
    public function testPostSaveTaxCategories_AcceptsCjkName(): void
    {
        $employeeId = $this->createTaxesEmployee();
        $this->loginAsTaxesEmployee($employeeId);

        $response = $this->post('/taxes/save_tax_categories', [
            'tax_category_id'    => ['-1'],
            'tax_category'       => ['消費税'],
            'tax_group_sequence' => ['1'],
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);
    }

    /**
     * Regression test: `jurisdiction_name[]` containing `<`/`>` must be rejected.
     */
    public function testPostSaveTaxJurisdictions_RejectsMaliciousName(): void
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
    public function testPostSaveTaxJurisdictions_AcceptsUnicodeName(): void
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

    /**
     * Regression test: a negative tax rate must be
     * rejected by Taxes::postSave so it can never be persisted to tax_code_rate.
     */
    public function testPostSave_RejectsNegativeTaxRate(): void
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

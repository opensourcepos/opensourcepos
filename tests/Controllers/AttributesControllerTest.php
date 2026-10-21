<?php

namespace Tests\Controllers;

use App\Models\Attribute;
use CodeIgniter\Database\Config;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\EmployeeFixtureTrait;

class AttributesControllerTest extends CIUnitTestCase
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

    protected function createAttributesEmployee(): int
    {
        return $this->createEmployee(
            grants: [
                ['permission_id' => 'attributes', 'menu_group' => 'home'],
            ]
        );
    }

    protected function loginAsAttributesEmployee(int $personId): void
    {
        $this->withSession([
            'person_id'  => $personId,
            'menu_group' => 'home',
        ]);
    }

    public function testGetExportAll_StreamsCsvDownload(): void
    {
        $employeeId = $this->createAttributesEmployee();
        $this->loginAsAttributesEmployee($employeeId);

        $definitionName = 'Color' . uniqid();
        $definitionData = [
            'definition_name'  => $definitionName,
            'definition_unit'  => null,
            'definition_flags' => Attribute::SHOW_IN_ITEMS,
            'definition_fk'    => null,
            'definition_type'  => 'TEXT',
        ];

        model(Attribute::class)->saveDefinition($definitionData);

        $response = $this->get('/attributes/exportAll/csv');

        $response->assertStatus(200);
        $contentDisposition = $response->getHeaderLine('Content-Disposition');
        $this->assertStringContainsString('attachment', $contentDisposition);
        $this->assertStringContainsString('attributes_', $contentDisposition);
        $this->assertStringContainsString($definitionName, $response->getBody());
    }

    public function testGetExportAll_StreamsPdfDownload(): void
    {
        $employeeId = $this->createAttributesEmployee();
        $this->loginAsAttributesEmployee($employeeId);

        $definitionData = [
            'definition_name'  => 'Size' . uniqid(),
            'definition_unit'  => null,
            'definition_flags' => Attribute::SHOW_IN_ITEMS,
            'definition_fk'    => null,
            'definition_type'  => 'TEXT',
        ];

        model(Attribute::class)->saveDefinition($definitionData);

        $response = $this->get('/attributes/exportAll/pdf');

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->getHeaderLine('Content-Type'));
    }

    public function testGetExportAll_CapturesRawDefinitionFlagsBeforeResolution(): void
    {
        $employeeId = $this->createAttributesEmployee();
        $this->loginAsAttributesEmployee($employeeId);

        $rawFlags = Attribute::SHOW_IN_ITEMS | Attribute::SHOW_IN_SALES;

        $definitionData = [
            'definition_name'  => 'Material' . uniqid(),
            'definition_unit'  => null,
            'definition_flags' => $rawFlags,
            'definition_fk'    => null,
            'definition_type'  => 'TEXT',
        ];

        model(Attribute::class)->saveDefinition($definitionData);

        $response = $this->get('/attributes/exportAll/sql');

        $response->assertStatus(200);
        $body = $response->getBody();
        $this->assertStringContainsString('INSERT INTO', $body);
        $this->assertStringContainsString((string) $rawFlags, $body);
    }
}

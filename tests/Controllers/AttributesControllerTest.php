<?php

namespace Tests\Controllers;

use App\Models\Attribute;
use CodeIgniter\Database\Config;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use ReflectionProperty;
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

    // Headers (Content-Disposition etc.) are only built in DownloadResponse::send(),
    // which the test harness never calls; force it so header assertions work.
    // getBody() is also empty for DownloadResponse - its content lives in the
    // private $binary property.
    protected function downloadResponseBody(ResponseInterface $response): string
    {
        if ($response instanceof DownloadResponse) {
            $response->buildHeaders();

            $reflection = new ReflectionProperty($response, 'binary');
            $reflection->setAccessible(true);

            return (string) $reflection->getValue($response);
        }

        return (string) $response->getBody();
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

        $response = $this->get('/attributes/exportAll/csv?search=');

        $response->assertStatus(200);
        $body = $this->downloadResponseBody($response->response());

        $contentDisposition = $response->response()->getHeaderLine('Content-Disposition');
        $this->assertStringContainsString('attachment', $contentDisposition);
        $this->assertStringContainsString('attributes_', $contentDisposition);
        $this->assertStringContainsString($definitionName, $body);
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

        $response = $this->get('/attributes/exportAll/pdf?search=');

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->response()->getHeaderLine('Content-Type'));
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

        $response = $this->get('/attributes/exportAll/sql?search=');

        $response->assertStatus(200);
        $body = $this->downloadResponseBody($response->response());
        $this->assertStringContainsString('INSERT INTO', $body);
        $this->assertStringContainsString((string) $rawFlags, $body);
    }
}

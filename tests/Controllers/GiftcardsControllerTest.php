<?php

namespace Tests\Controllers;

use App\Models\Giftcard;
use CodeIgniter\Database\Config;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use ReflectionProperty;
use Tests\Support\EmployeeFixtureTrait;

class GiftcardsControllerTest extends CIUnitTestCase
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

    protected function createGiftcardsEmployee(): int
    {
        return $this->createEmployee(
            grants: [
                ['permission_id' => 'giftcards', 'menu_group' => 'home'],
            ]
        );
    }

    protected function loginAsGiftcardsEmployee(int $personId): void
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
        $employeeId = $this->createGiftcardsEmployee();
        $this->loginAsGiftcardsEmployee($employeeId);

        $giftcardNumber = 'GC' . uniqid();
        $giftcardData = [
            'record_time'     => date('Y-m-d H:i:s'),
            'giftcard_number' => $giftcardNumber,
            'value'           => '25.00',
            'person_id'       => null,
        ];
        model(Giftcard::class)->save_value($giftcardData);

        $response = $this->get('/giftcards/exportAll/csv?search=');

        $response->assertStatus(200);
        $body = $this->downloadResponseBody($response->response());

        $contentDisposition = $response->response()->getHeaderLine('Content-Disposition');
        $this->assertStringContainsString('attachment', $contentDisposition);
        $this->assertStringContainsString('giftcards_', $contentDisposition);
        $this->assertStringContainsString($giftcardNumber, $body);
    }

    public function testGetExportAll_StreamsPdfDownload(): void
    {
        $employeeId = $this->createGiftcardsEmployee();
        $this->loginAsGiftcardsEmployee($employeeId);

        $giftcardData = [
            'record_time'     => date('Y-m-d H:i:s'),
            'giftcard_number' => 'GC' . uniqid(),
            'value'           => '25.00',
            'person_id'       => null,
        ];
        model(Giftcard::class)->save_value($giftcardData);

        $response = $this->get('/giftcards/exportAll/pdf?search=');

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->response()->getHeaderLine('Content-Type'));
    }
}

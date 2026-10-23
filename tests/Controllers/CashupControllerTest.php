<?php

namespace Tests\Controllers;

use App\Models\Appconfig;
use App\Models\Cashup;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use ReflectionProperty;
use Tests\Support\DatabaseBootstrapTrait;
use Tests\Support\EmployeeFixtureTrait;

class CashupControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use DatabaseBootstrapTrait;
    use FeatureTestTrait;
    use EmployeeFixtureTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $seedOnce    = true;
    protected $refresh     = false;
    protected $namespace   = null;

    protected function setUp(): void
    {
        $this->resetDatabaseOnce();

        parent::setUp();

        model(Appconfig::class)->batch_save([
            'company'             => 'Test Co',
            'dateformat'          => 'Y-m-d',
            'timeformat'          => 'H:i:s',
            'number_locale'       => 'en_US',
            'currency_decimals'   => 2,
            'thousands_separator' => ',',
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    protected function createCashupEmployee(): int
    {
        return $this->createEmployee(
            first_name: 'Cashier',
            last_name:  'Cashup',
            email:      'cashup.' . uniqid() . '@test.com',
            username:   'cashup_' . uniqid(),
            grants: [
                ['permission_id' => 'cashups', 'menu_group' => 'home'],
            ],
        );
    }

    protected function loginAs(int $personId): void
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

    public function testTamperedTotalIsRecomputedServerSide(): void
    {
        $cashierId = $this->createCashupEmployee();
        $this->loginAs($cashierId);

        // Component amounts only sum to (50 - 0 - 0 + 10 + 0 + 0) = 60, but the
        // client claims a total of 1000.00. The server must store 60, not 1000.
        $response = $this->post('/cashups/save/-1', [
            'open_date'            => '2026-09-05 08:00:00',
            'close_date'           => '2026-09-05 16:00:00',
            'open_amount_cash'     => '0',
            'transfer_amount_cash' => '0',
            'closed_amount_cash'   => '50',
            'closed_amount_due'    => '10',
            'closed_amount_card'   => '0',
            'closed_amount_check'  => '0',
            'closed_amount_total'  => '1000.00',
            'open_employee_id'     => '999999',
            'close_employee_id'    => '999999',
            'description'          => 'Test cashup - tampered total',
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $cashup = model(Cashup::class)->get_info($result['id']);
        $this->assertEquals(60.0, (float) $cashup->closed_amount_total);
    }

    public function testConsistentTotalIsStoredAndOwnerIsForcedToAuthenticatedUser(): void
    {
        $cashierId = $this->createCashupEmployee();
        $this->loginAs($cashierId);

        // 100 - 20 - 5 + 15 + 40 + 25 = 155. The client also tries to attribute
        // the cashup to other employees (777/888); the server must force the
        // authenticated user's person_id.
        $response = $this->post('/cashups/save/-1', [
            'open_date'            => '2026-09-05 08:00:00',
            'close_date'           => '2026-09-05 16:00:00',
            'open_amount_cash'     => '20',
            'transfer_amount_cash' => '5',
            'closed_amount_cash'   => '100',
            'closed_amount_due'    => '15',
            'closed_amount_card'   => '40',
            'closed_amount_check'  => '25',
            'closed_amount_total'  => '155',
            'open_employee_id'     => '777',
            'close_employee_id'    => '888',
            'description'          => 'Test cashup - consistent total',
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $cashup = model(Cashup::class)->get_info($result['id']);
        $this->assertEquals(155.0, (float) $cashup->closed_amount_total);
        $this->assertEquals($cashierId, (int) $cashup->open_employee_id);
        $this->assertEquals($cashierId, (int) $cashup->close_employee_id);
    }

    public function testGetExportAll_StreamsCsvDownload(): void
    {
        $cashierId = $this->createCashupEmployee();
        $this->loginAs($cashierId);

        $cashUpData = [
            'open_date'            => '2026-09-05 08:00:00',
            'close_date'           => '2026-09-05 16:00:00',
            'open_amount_cash'     => '20',
            'transfer_amount_cash' => '5',
            'closed_amount_cash'   => '100',
            'closed_amount_due'    => '15',
            'closed_amount_card'   => '40',
            'closed_amount_check'  => '25',
            'closed_amount_total'  => '169.42',
            'note'                 => false,
            'description'          => 'Export-all CSV cashup',
            'open_employee_id'     => $cashierId,
            'close_employee_id'    => $cashierId,
            'deleted'              => false,
        ];
        model(Cashup::class)->save_value($cashUpData);

        $response = $this->get('/cashups/exportall/csv?search=&start_date=2026-09-01&end_date=2026-09-30');

        $response->assertStatus(200);
        $body = $this->downloadResponseBody($response->response());

        $response->assertHeader('Content-Disposition');
        $this->assertStringContainsString('attachment', $response->response()->getHeaderLine('Content-Disposition'));
        $this->assertStringContainsString('cashups_', $response->response()->getHeaderLine('Content-Disposition'));
        $this->assertStringContainsString('169.42', $body);
    }

    public function testGetExportAll_StreamsPdfDownload(): void
    {
        $cashierId = $this->createCashupEmployee();
        $this->loginAs($cashierId);

        $cashUpData = [
            'open_date'            => '2026-09-05 08:00:00',
            'close_date'           => '2026-09-05 16:00:00',
            'open_amount_cash'     => '20',
            'transfer_amount_cash' => '5',
            'closed_amount_cash'   => '100',
            'closed_amount_due'    => '15',
            'closed_amount_card'   => '40',
            'closed_amount_check'  => '25',
            'closed_amount_total'  => '155',
            'note'                 => false,
            'description'          => 'Export-all PDF cashup',
            'open_employee_id'     => $cashierId,
            'close_employee_id'    => $cashierId,
            'deleted'              => false,
        ];
        model(Cashup::class)->save_value($cashUpData);

        $response = $this->get('/cashups/exportall/pdf?search=&start_date=2026-09-01&end_date=2026-09-30');

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->response()->getHeaderLine('Content-Type'));
    }
}

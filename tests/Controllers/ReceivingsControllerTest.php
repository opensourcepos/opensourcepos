<?php

namespace Tests\Controllers;

use App\Libraries\Receiving_lib;
use App\Models\Stock_location;
use CodeIgniter\Config\Services;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Config\OSPOS;
use Tests\Support\DatabaseBootstrapTrait;
use Tests\Support\EmployeeFixtureTrait;
use Tests\Support\ItemFixtureTrait;
use Tests\Support\OsposConfigFixtureTrait;

class ReceivingsControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use DatabaseBootstrapTrait;
    use FeatureTestTrait;
    use EmployeeFixtureTrait;
    use ItemFixtureTrait;
    use OsposConfigFixtureTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $seedOnce    = true;
    protected $refresh     = false;
    protected $namespace   = null;

    private Stock_location $stock_location;

    protected function setUp(): void
    {
        $this->resetDatabaseOnce();
        parent::setUp();
        config(OSPOS::class)->update_settings();
        $this->stock_location = model(Stock_location::class);
    }

    protected function loginAsReceivingsEmployee(int $personId): void
    {
        $this->withSession([
            'person_id'  => $personId,
            'menu_group' => 'home',
        ]);
    }

    public function testPostChangeMode_RejectsUnauthorizedDestination(): void
    {
        $suffixA = bin2hex(random_bytes(8));
        $suffixB = bin2hex(random_bytes(8));

        $locationA = ['location_name' => "RecvAuth_{$suffixA}", 'deleted' => 0];
        $this->assertTrue($this->stock_location->saveValue($locationA, NEW_ENTRY));

        $locationB = ['location_name' => "RecvUnauth_{$suffixB}", 'deleted' => 0];
        $this->assertTrue($this->stock_location->saveValue($locationB, NEW_ENTRY));

        $locAId = (int) $locationA['location_id'];
        $locBId = (int) $locationB['location_id'];

        $personId = $this->createEmployee(
            grants: [
                ['permission_id' => 'receivings', 'menu_group' => 'home'],
                ['permission_id' => 'receivings_' . $locationA['location_name'], 'menu_group' => 'home'],
            ]
        );
        $this->loginAsReceivingsEmployee($personId);

        $response = $this->post('/receivings/changeMode', [
            'stock_source'      => $locAId,
            'stock_destination' => $locBId,
            'mode'              => 'requisition',
        ]);

        $response->assertStatus(200);
        $this->assertNotEquals((string) $locBId, Services::session()->get('recv_stock_destination'));
    }

    public function testPostChangeMode_AcceptsAuthorizedDestination(): void
    {
        $suffix = bin2hex(random_bytes(8));

        $locationA = ['location_name' => "RecvAllowed_{$suffix}", 'deleted' => 0];
        $this->assertTrue($this->stock_location->saveValue($locationA, NEW_ENTRY));

        $locAId = (int) $locationA['location_id'];

        $personId = $this->createEmployee(
            grants: [
                ['permission_id' => 'receivings', 'menu_group' => 'home'],
                ['permission_id' => 'receivings_' . $locationA['location_name'], 'menu_group' => 'home'],
            ]
        );
        $this->loginAsReceivingsEmployee($personId);

        $response = $this->post('/receivings/changeMode', [
            'stock_source'      => $locAId,
            'stock_destination' => $locAId,
            'mode'              => 'normal',
        ]);

        $response->assertStatus(200);
        $this->assertEquals((string) $locAId, Services::session()->get('recv_stock_destination'));
    }

    public function testPostChangeMode_RejectsUnauthorizedSource(): void
    {
        $suffixA = bin2hex(random_bytes(8));
        $suffixB = bin2hex(random_bytes(8));

        $locationA = ['location_name' => "RecvSrcAuth_{$suffixA}", 'deleted' => 0];
        $this->assertTrue($this->stock_location->saveValue($locationA, NEW_ENTRY));

        $locationB = ['location_name' => "RecvSrcUnauth_{$suffixB}", 'deleted' => 0];
        $this->assertTrue($this->stock_location->saveValue($locationB, NEW_ENTRY));

        $locBId = (int) $locationB['location_id'];

        $personId = $this->createEmployee(
            grants: [
                ['permission_id' => 'receivings', 'menu_group' => 'home'],
                ['permission_id' => 'receivings_' . $locationA['location_name'], 'menu_group' => 'home'],
            ]
        );
        $this->loginAsReceivingsEmployee($personId);

        $response = $this->post('/receivings/changeMode', [
            'stock_source' => $locBId,
            'mode'         => 'receive',
        ]);

        $response->assertStatus(200);
        $this->assertNotEquals($locBId, Services::session()->get('recv_stock_source'));
    }

    public function testPostChangeMode_AcceptsAuthorizedSource(): void
    {
        $suffix = bin2hex(random_bytes(8));

        $locationA = ['location_name' => "RecvSrcAllowed_{$suffix}", 'deleted' => 0];
        $this->assertTrue($this->stock_location->saveValue($locationA, NEW_ENTRY));

        $locAId = (int) $locationA['location_id'];

        $personId = $this->createEmployee(
            grants: [
                ['permission_id' => 'receivings', 'menu_group' => 'home'],
                ['permission_id' => 'receivings_' . $locationA['location_name'], 'menu_group' => 'home'],
            ]
        );
        $this->loginAsReceivingsEmployee($personId);

        $response = $this->post('/receivings/changeMode', [
            'stock_source' => $locAId,
            'mode'         => 'receive',
        ]);

        $response->assertStatus(200);
        $this->assertEquals($locAId, Services::session()->get('recv_stock_source'));
    }

    public function testPostEditItem_AcceptsCommaDecimalInCommaLocale(): void
    {
        $suffix = bin2hex(random_bytes(8));
        $location = ['location_name' => "RecvEdit_{$suffix}", 'deleted' => 0];
        $this->assertTrue($this->stock_location->saveValue($location, NEW_ENTRY));
        $locId = (int) $location['location_id'];

        $personId = $this->createEmployee(
            grants: [
                ['permission_id' => 'receivings', 'menu_group' => 'home'],
                ['permission_id' => 'receivings_' . $location['location_name'], 'menu_group' => 'home'],
                ['permission_id' => 'items', 'menu_group' => 'home'],
                ['permission_id' => 'items_' . $location['location_name'], 'menu_group' => 'home'],
            ]
        );
        $this->loginAsReceivingsEmployee($personId);
        Services::session()->set('person_id', $personId);

        $settings = config(OSPOS::class)->settings;
        $settings['number_locale'] = 'de_DE';
        $settings['currency_decimals'] = 2;
        $settings['quantity_decimals'] = 2;
        $settings['thousands_separator'] = '1';
        $settings['multi_pack_enabled'] = '0';
        $this->injectOsposSettings($settings);

        $itemId = $this->createTestItem(HAS_STOCK);
        $receivingLib = new Receiving_lib();
        $receivingLib->empty_cart();
        $this->assertTrue($receivingLib->add_item((string) $itemId, 1, $locId));

        $response = $this->withSession([
            'person_id'         => $personId,
            'menu_group'        => 'home',
            'recv_cart'         => $receivingLib->get_cart(),
            'recv_stock_source' => $locId,
        ])->post('/receivings/editItem/1', [
            'price'              => '12,50',
            'quantity'           => '2,50',
            'receiving_quantity' => '1',
            'discount'           => '1,25',
            'discount_type'      => '0',
            'description'        => 'Updated desc',
            'serialnumber'       => '',
        ]);

        $response->assertStatus(200);
        $cart = Services::session()->get('recv_cart');
        $this->assertEqualsWithDelta(12.50, (float) $cart[1]['price'], 0.001);
        $this->assertEqualsWithDelta(2.50, (float) $cart[1]['quantity'], 0.001);
        $this->assertEqualsWithDelta(1.25, (float) $cart[1]['discount'], 0.001);
    }
}

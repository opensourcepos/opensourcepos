<?php

namespace Tests\Libraries;

use App\Libraries\Receiving_lib;
use App\Models\Receiving;
use App\Models\Stock_location;
use CodeIgniter\Config\Services;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\OSPOS;
use Tests\Support\DatabaseBootstrapTrait;
use Tests\Support\EmployeeFixtureTrait;
use Tests\Support\ItemFixtureTrait;

class Receiving_libTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use DatabaseBootstrapTrait;
    use EmployeeFixtureTrait;
    use ItemFixtureTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $seedOnce    = true;
    protected $refresh     = false;
    protected $namespace   = null;

    private Receiving_lib $receivingLib;
    private Stock_location $stockLocation;

    protected function setUp(): void
    {
        $this->resetDatabaseOnce();
        parent::setUp();
        config(OSPOS::class)->update_settings();
        Services::session()->set('person_id', 1);
        $this->receivingLib = new Receiving_lib();
        $this->receivingLib->clear_all();
        $this->receivingLib->clear_stock_source();
        $this->receivingLib->clear_stock_destination();
        $this->stockLocation = model(Stock_location::class);
    }

    public function testSetStockSource_RejectsUnauthorizedLocation(): void
    {
        $suffix = bin2hex(random_bytes(8));
        $location = ['location_name' => "LibUnauth_{$suffix}", 'deleted' => 0];
        $this->assertTrue($this->stockLocation->saveValue($location, NEW_ENTRY));
        $locId = (int) $location['location_id'];

        $personId = $this->createEmployee(
            grants: [
                ['permission_id' => 'receivings', 'menu_group' => 'home'],
            ]
        );
        Services::session()->set('person_id', $personId);

        $this->receivingLib->set_stock_source($locId);

        $this->assertNotEquals($locId, Services::session()->get('recv_stock_source'));
    }

    public function testSetStockDestination_RejectsUnauthorizedLocation(): void
    {
        $suffix = bin2hex(random_bytes(8));
        $location = ['location_name' => "LibDestUnauth_{$suffix}", 'deleted' => 0];
        $this->assertTrue($this->stockLocation->saveValue($location, NEW_ENTRY));
        $locId = (int) $location['location_id'];

        $personId = $this->createEmployee(
            grants: [
                ['permission_id' => 'receivings', 'menu_group' => 'home'],
            ]
        );
        Services::session()->set('person_id', $personId);

        $this->receivingLib->set_stock_destination((string) $locId);

        $this->assertNotEquals((string) $locId, Services::session()->get('recv_stock_destination'));
    }

    public function testAddItem_AcceptsFractionalQuantity(): void
    {
        $itemId = $this->createTestItem(HAS_STOCK);

        $result = $this->receivingLib->add_item((string) $itemId, 1.5, 1);

        $this->assertTrue($result);
        $cart = $this->receivingLib->get_cart();
        $this->assertEqualsWithDelta(1.5, (float) $cart[1]['quantity'], 0.001);
    }

    public function testAddItem_PreservesEditedPriceAndDiscountWhenAddingDuplicateItem(): void
    {
        $itemId = $this->createTestItem(HAS_STOCK);

        $this->assertTrue($this->receivingLib->add_item((string) $itemId, 2, 1, 0, PERCENT, 10.00));
        $this->assertTrue($this->receivingLib->edit_item(1, 'Edited', '', 2, 5.00, FIXED, 20.00, 1));

        $this->assertTrue($this->receivingLib->add_item((string) $itemId, 1, 1));

        $cart = $this->receivingLib->get_cart();
        $this->assertEqualsWithDelta(3.0, (float) $cart[1]['quantity'], 0.001);
        $this->assertEqualsWithDelta(20.00, (float) $cart[1]['price'], 0.001);
        $this->assertEqualsWithDelta(5.00, (float) $cart[1]['discount'], 0.001);
        $this->assertSame(FIXED, $cart[1]['discount_type']);
        $this->assertEqualsWithDelta(55.00, (float) $cart[1]['total'], 0.001);
    }

    public function testEditItem_ReturnsTrueAndPreservesDiscountTypeWhenNull(): void
    {
        $itemId = $this->createTestItem(HAS_STOCK);

        $this->assertTrue($this->receivingLib->add_item((string) $itemId, 2, 1, 3.00, FIXED, 10.00));

        $result = $this->receivingLib->edit_item(1, 'Desc', '', 2.5, 5.00, null, 10.00, 1);

        $this->assertTrue($result);
        $cart = $this->receivingLib->get_cart();
        $this->assertSame(FIXED, $cart[1]['discount_type']);
        $this->assertEqualsWithDelta(20.00, (float) $cart[1]['total'], 0.001);
    }

    public function testReturnEntireReceiving_AcceptsStringReceiptAndHandlesNullSupplier(): void
    {
        $employeeId = $this->createEmployee();
        $itemId = $this->createTestItem(HAS_STOCK);
        $receivingModel = model(Receiving::class);

        $receivingId = $receivingModel->save_value(
            [
                0 => [
                    'item_id'            => $itemId,
                    'line'               => 0,
                    'description'        => 'Item',
                    'serialnumber'       => '',
                    'quantity'           => 4,
                    'receiving_quantity' => 1,
                    'discount'           => 0,
                    'discount_type'      => PERCENT,
                    'price'              => 5.00,
                    'item_location'      => 1,
                ],
            ],
            -1,
            $employeeId,
            'No supplier',
            'REF-NONE',
            'Cash'
        );

        $this->receivingLib->return_entire_receiving('NONEXISTENT_REF');
        $this->assertEmpty($this->receivingLib->get_cart());

        $this->receivingLib->return_entire_receiving('RECV ' . $receivingId);
        $cart = $this->receivingLib->get_cart();
        $this->assertCount(1, $cart);
        $this->assertEqualsWithDelta(-4.0, (float) $cart[1]['quantity'], 0.001);
        $this->assertSame(-1, $this->receivingLib->get_supplier());
    }

    public function testCopyEntireReceiving_HandlesReceivingWithoutSupplier(): void
    {
        $employeeId = $this->createEmployee();
        $itemId = $this->createTestItem(HAS_STOCK);
        $receivingModel = model(Receiving::class);

        $receivingId = $receivingModel->save_value(
            [
                0 => [
                    'item_id'            => $itemId,
                    'line'               => 0,
                    'description'        => 'Item',
                    'serialnumber'       => '',
                    'quantity'           => 3,
                    'receiving_quantity' => 1,
                    'discount'           => 0,
                    'discount_type'      => PERCENT,
                    'price'              => 5.00,
                    'item_location'      => 1,
                ],
            ],
            -1,
            $employeeId,
            'No supplier',
            'REF-COPY',
            'Cash'
        );

        $this->receivingLib->copy_entire_receiving($receivingId);

        $cart = $this->receivingLib->get_cart();
        $this->assertCount(1, $cart);
        $this->assertSame(-1, $this->receivingLib->get_supplier());
    }
}

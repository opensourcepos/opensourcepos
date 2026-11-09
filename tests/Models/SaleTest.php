<?php

namespace Tests\Models;

use App\Models\Dinner_table;
use App\Models\Rewards;
use App\Models\Sale;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;
use Config\OSPOS;
use Tests\Support\DatabaseBootstrapTrait;
use Tests\Support\EmployeeFixtureTrait;
use Tests\Support\FailedQueryConnectionTrait;
use Tests\Support\ItemFixtureTrait;

/**
 * Regression tests: Sale::save_value() must reject
 * (and roll back) a payment that would overdraw a gift card or a customer's
 * reward points, instead of silently applying a stale/negative balance.
 */
class SaleTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use DatabaseBootstrapTrait;
    use EmployeeFixtureTrait;
    use FailedQueryConnectionTrait;
    use ItemFixtureTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $seedOnce    = true;
    protected $refresh     = false;
    protected $namespace   = null;

    private const LOCATION_ID = 1;

    protected function setUp(): void
    {
        $this->resetDatabaseOnce();

        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    protected function createGiftcard(float $value): int
    {
        $giftcardNumber = random_int(1000000, 9999999);

        \Config\Database::connect()->table('giftcards')->insert([
            'giftcard_number' => $giftcardNumber,
            'value'           => $value,
            'deleted'         => 0,
            'person_id'       => null,
        ]);

        return $giftcardNumber;
    }

    protected function createCustomerWithPoints(int $points): int
    {
        $db = \Config\Database::connect();

        $db->table('people')->insert([
            'first_name'   => 'Reward',
            'last_name'    => 'Customer',
            'phone_number' => '555-0300',
            'email'        => 'customer-' . uniqid() . '@test.com',
            'address_1'    => '',
            'address_2'    => '',
            'city'         => '',
            'state'        => '',
            'zip'          => '',
            'country'      => '',
            'comments'     => '',
        ]);
        $personId = (int) $db->insertID();

        $db->table('customers')->insert([
            'person_id'   => $personId,
            'employee_id' => 1,
            'points'      => $points,
        ]);

        return $personId;
    }

    protected function buildCartLine(int $itemId, float $quantity, float $price): array
    {
        return [
            0 => [
                'item_id'       => $itemId,
                'line'          => 0,
                'description'   => 'Test Item',
                'serialnumber'  => '',
                'quantity'      => $quantity,
                'discount'      => 0,
                'discount_type' => 0,
                'cost_price'    => 1.00,
                'price'         => $price,
                'item_location' => self::LOCATION_ID,
                'print_option'  => 0,
            ],
        ];
    }

    protected function countSalesRows(): int
    {
        return \Config\Database::connect()->table('sales')->countAllResults();
    }

    protected function getGiftcardValue(int $giftcardNumber): float
    {
        $row = \Config\Database::connect()->table('giftcards')
            ->where('giftcard_number', $giftcardNumber)
            ->get()
            ->getRow();

        return (float) $row->value;
    }

    protected function getCustomerPoints(int $personId): int
    {
        $row = \Config\Database::connect()->table('customers')
            ->where('person_id', $personId)
            ->get()
            ->getRow();

        return (int) $row->points;
    }

    public function testSaveValueSucceedsWithSufficientGiftcardBalance(): void
    {
        $employeeId     = $this->createEmployee();
        $giftcardNumber = $this->createGiftcard(100.00);
        $itemId         = $this->createTestItem(HAS_NO_STOCK);

        $saleModel  = model(Sale::class);
        $saleStatus = COMPLETED;
        $items      = $this->buildCartLine($itemId, 1, 60.00);
        $payments   = [
            0 => [
                'payment_type'    => lang('Sales.giftcard') . ':' . $giftcardNumber,
                'payment_amount'  => 60.00,
                'cash_refund'     => 0,
                'cash_adjustment' => 0,
                'reference_code'  => null,
            ],
        ];
        $salesTaxes = [[], []];

        $result = $saleModel->save_value(
            NEW_ENTRY,
            $saleStatus,
            $items,
            NEW_ENTRY,
            $employeeId,
            'test sale',
            null,
            null,
            null,
            SALE_TYPE_POS,
            $payments,
            null,
            $salesTaxes
        );

        $this->assertGreaterThan(0, $result);
        $this->assertEqualsWithDelta(40.00, $this->getGiftcardValue($giftcardNumber), 0.001);
    }

    public function testSaveValueDoesNotMatchGiftcardSinkOnSubstringOnly(): void
    {
        $employeeId     = $this->createEmployee();
        $giftcardNumber = $this->createGiftcard(100.00);
        $itemId         = $this->createTestItem(HAS_NO_STOCK);

        $saleModel  = model(Sale::class);
        $saleStatus = COMPLETED;
        $items      = $this->buildCartLine($itemId, 1, 60.00);
        $payments   = [
            0 => [
                'payment_type'    => 'Foo ' . lang('Sales.giftcard') . ':' . $giftcardNumber,
                'payment_amount'  => 60.00,
                'cash_refund'     => 0,
                'cash_adjustment' => 0,
                'reference_code'  => null,
            ],
        ];
        $salesTaxes = [[], []];

        $result = $saleModel->save_value(
            NEW_ENTRY,
            $saleStatus,
            $items,
            NEW_ENTRY,
            $employeeId,
            'test sale',
            null,
            null,
            null,
            SALE_TYPE_POS,
            $payments,
            null,
            $salesTaxes
        );

        $this->assertGreaterThan(0, $result);
        $this->assertEqualsWithDelta(100.00, $this->getGiftcardValue($giftcardNumber), 0.001);
    }

    public function testSaveValueReturnsInsufficientGiftcardBalanceSentinel(): void
    {
        $employeeId     = $this->createEmployee();
        $giftcardNumber = $this->createGiftcard(50.00);
        $itemId         = $this->createTestItem(HAS_NO_STOCK);

        $salesCountBefore = $this->countSalesRows();

        $saleModel  = model(Sale::class);
        $saleStatus = COMPLETED;
        $items      = $this->buildCartLine($itemId, 1, 60.00);
        $payments   = [
            0 => [
                'payment_type'    => lang('Sales.giftcard') . ':' . $giftcardNumber,
                'payment_amount'  => 60.00,
                'cash_refund'     => 0,
                'cash_adjustment' => 0,
                'reference_code'  => null,
            ],
        ];
        $salesTaxes = [[], []];

        $result = $saleModel->save_value(
            NEW_ENTRY,
            $saleStatus,
            $items,
            NEW_ENTRY,
            $employeeId,
            'test sale',
            null,
            null,
            null,
            SALE_TYPE_POS,
            $payments,
            null,
            $salesTaxes
        );

        $this->assertSame(INSUFFICIENT_GIFTCARD_BALANCE, $result);
        $this->assertEqualsWithDelta(50.00, $this->getGiftcardValue($giftcardNumber), 0.001);
        $this->assertSame($salesCountBefore, $this->countSalesRows());
    }

    public function testSaveValueReturnsInsufficientRewardPointsSentinel(): void
    {
        $employeeId = $this->createEmployee();
        $customerId = $this->createCustomerWithPoints(20);
        $itemId     = $this->createTestItem(HAS_NO_STOCK);

        $salesCountBefore = $this->countSalesRows();

        $saleModel  = model(Sale::class);
        $saleStatus = COMPLETED;
        $items      = $this->buildCartLine($itemId, 1, 60.00);
        $payments   = [
            0 => [
                'payment_type'    => lang('Sales.rewards'),
                'payment_amount'  => 40.00,
                'cash_refund'     => 0,
                'cash_adjustment' => 0,
                'reference_code'  => null,
            ],
        ];
        $salesTaxes = [[], []];

        $result = $saleModel->save_value(
            NEW_ENTRY,
            $saleStatus,
            $items,
            $customerId,
            $employeeId,
            'test sale',
            null,
            null,
            null,
            SALE_TYPE_POS,
            $payments,
            null,
            $salesTaxes
        );

        $this->assertSame(INSUFFICIENT_REWARD_POINTS, $result);
        $this->assertSame(20, $this->getCustomerPoints($customerId));
        $this->assertSame($salesCountBefore, $this->countSalesRows());
    }

    public function testSaveValueDecrementsStockOnCompletedSale(): void
    {
        $employeeId = $this->createEmployee();
        $itemId     = $this->createTestItem(HAS_STOCK);

        \Config\Database::connect()->table('item_quantities')->insert([
            'item_id'     => $itemId,
            'location_id' => self::LOCATION_ID,
            'quantity'    => 10,
        ]);

        $saleModel  = model(Sale::class);
        $saleStatus = COMPLETED;
        $items      = $this->buildCartLine($itemId, 3, 5.00);
        $payments   = [
            0 => [
                'payment_type'    => lang('Sales.cash'),
                'payment_amount'  => 15.00,
                'cash_refund'     => 0,
                'cash_adjustment' => 0,
                'reference_code'  => null,
            ],
        ];
        $salesTaxes = [[], []];

        $result = $saleModel->save_value(
            NEW_ENTRY,
            $saleStatus,
            $items,
            NEW_ENTRY,
            $employeeId,
            'test sale',
            null,
            null,
            null,
            SALE_TYPE_POS,
            $payments,
            null,
            $salesTaxes
        );

        $this->assertGreaterThan(0, $result);
        $this->assertEqualsWithDelta(7.0, $this->getItemQuantity($itemId, self::LOCATION_ID), 0.001);
    }

    public function testSaveValueDecrementsStockOnCompletedSaleWhenNoQuantityRowExists(): void
    {
        $employeeId = $this->createEmployee();
        $itemId     = $this->createTestItem(HAS_STOCK);

        // Deliberately no item_quantities row inserted for this item/location.

        $saleModel  = model(Sale::class);
        $saleStatus = COMPLETED;
        $items      = $this->buildCartLine($itemId, 3, 5.00);
        $payments   = [
            0 => [
                'payment_type'    => lang('Sales.cash'),
                'payment_amount'  => 15.00,
                'cash_refund'     => 0,
                'cash_adjustment' => 0,
                'reference_code'  => null,
            ],
        ];
        $salesTaxes = [[], []];

        $result = $saleModel->save_value(
            NEW_ENTRY,
            $saleStatus,
            $items,
            NEW_ENTRY,
            $employeeId,
            'test sale',
            null,
            null,
            null,
            SALE_TYPE_POS,
            $payments,
            null,
            $salesTaxes
        );

        $this->assertGreaterThan(0, $result);
        $this->assertEqualsWithDelta(-3.0, $this->getItemQuantity($itemId, self::LOCATION_ID), 0.001);
    }

    public function testDeleteRestoresStockOnCanceledSale(): void
    {
        $employeeId = $this->createEmployee();
        $itemId     = $this->createTestItem(HAS_STOCK);

        \Config\Database::connect()->table('item_quantities')->insert([
            'item_id'     => $itemId,
            'location_id' => self::LOCATION_ID,
            'quantity'    => 10,
        ]);

        $saleModel  = model(Sale::class);
        $saleStatus = COMPLETED;
        $items      = $this->buildCartLine($itemId, 3, 5.00);
        $payments   = [
            0 => [
                'payment_type'    => lang('Sales.cash'),
                'payment_amount'  => 15.00,
                'cash_refund'     => 0,
                'cash_adjustment' => 0,
                'reference_code'  => null,
            ],
        ];
        $salesTaxes = [[], []];

        $saleId = $saleModel->save_value(
            NEW_ENTRY,
            $saleStatus,
            $items,
            NEW_ENTRY,
            $employeeId,
            'test sale',
            null,
            null,
            null,
            SALE_TYPE_POS,
            $payments,
            null,
            $salesTaxes
        );

        $this->assertGreaterThan(0, $saleId);
        $this->assertEqualsWithDelta(7.0, $this->getItemQuantity($itemId, self::LOCATION_ID), 0.001);

        $deleteResult = $saleModel->delete($saleId, false, true, $employeeId);

        $this->assertTrue($deleteResult);
        $this->assertEqualsWithDelta(10.0, $this->getItemQuantity($itemId, self::LOCATION_ID), 0.001);
    }

    public function testGetAllSuspended_ReturnsEmptyArrayWhenQueryFails(): void
    {
        $model = $this->modelWithFailingQuery(Sale::class);

        // Exercises both the NEW_ENTRY branch and the customer-id branch.
        $this->assertSame([], $model->get_all_suspended());
        $this->assertSame([], $model->get_all_suspended(NEW_ENTRY));
        $this->assertSame([], $model->get_all_suspended(123));
    }

    public function testSaveValueReturnCreditsGiftcardOnCompleted(): void
    {
        $employeeId     = $this->createEmployee();
        $giftcardNumber = $this->createGiftcard(50.00);
        $itemId         = $this->createTestItem(HAS_NO_STOCK);

        $saleModel  = model(Sale::class);
        $saleStatus = COMPLETED;
        $items      = $this->buildCartLine($itemId, 1, 30.00);
        $payments   = [
            0 => [
                'payment_type'    => lang('Sales.giftcard') . ':' . $giftcardNumber,
                'payment_amount'  => -30.00,
                'cash_refund'     => 0,
                'cash_adjustment' => 0,
                'reference_code'  => null,
            ],
        ];
        $salesTaxes = [[], []];

        $result = $saleModel->save_value(
            NEW_ENTRY,
            $saleStatus,
            $items,
            NEW_ENTRY,
            $employeeId,
            'test return',
            null,
            null,
            null,
            SALE_TYPE_RETURN,
            $payments,
            null,
            $salesTaxes
        );

        $this->assertGreaterThan(0, $result);
        $this->assertEqualsWithDelta(80.00, $this->getGiftcardValue($giftcardNumber), 0.001);
    }

    public function testSaveValueReturnSkipsGiftcardCreditOnSuspended(): void
    {
        $employeeId     = $this->createEmployee();
        $giftcardNumber = $this->createGiftcard(50.00);
        $itemId         = $this->createTestItem(HAS_NO_STOCK);

        $saleModel  = model(Sale::class);
        $saleStatus = SUSPENDED;
        $items      = $this->buildCartLine($itemId, 1, 30.00);
        $payments   = [
            0 => [
                'payment_type'    => lang('Sales.giftcard') . ':' . $giftcardNumber,
                'payment_amount'  => -30.00,
                'cash_refund'     => 0,
                'cash_adjustment' => 0,
                'reference_code'  => null,
            ],
        ];
        $salesTaxes = [[], []];

        $result = $saleModel->save_value(
            NEW_ENTRY,
            $saleStatus,
            $items,
            NEW_ENTRY,
            $employeeId,
            'test suspended return',
            null,
            null,
            null,
            SALE_TYPE_RETURN,
            $payments,
            null,
            $salesTaxes
        );

        $this->assertGreaterThan(0, $result);
        $this->assertEqualsWithDelta(50.00, $this->getGiftcardValue($giftcardNumber), 0.001);
    }

    public function testSaveValueReturnRejectsEmptyGiftcardNumber(): void
    {
        $employeeId = $this->createEmployee();
        $itemId     = $this->createTestItem(HAS_NO_STOCK);

        $salesCountBefore = $this->countSalesRows();

        $saleModel  = model(Sale::class);
        $saleStatus = COMPLETED;
        $items      = $this->buildCartLine($itemId, 1, 30.00);
        $payments   = [
            0 => [
                'payment_type'    => lang('Sales.giftcard') . ':',
                'payment_amount'  => -30.00,
                'cash_refund'     => 0,
                'cash_adjustment' => 0,
                'reference_code'  => null,
            ],
        ];
        $salesTaxes = [[], []];

        $result = $saleModel->save_value(
            NEW_ENTRY,
            $saleStatus,
            $items,
            NEW_ENTRY,
            $employeeId,
            'test return no card',
            null,
            null,
            null,
            SALE_TYPE_RETURN,
            $payments,
            null,
            $salesTaxes
        );

        $this->assertSame(INSUFFICIENT_GIFTCARD_BALANCE, $result);
        $this->assertSame($salesCountBefore, $this->countSalesRows());
    }

    public function testSaveValue_SkipsGiftcardAndRewardsDeductionWhenSuspendedAndAppliesOnceWhenCompleted(): void
    {
        $employeeId     = $this->createEmployee();
        $giftcardNumber = $this->createGiftcard(100.00);
        $customerId     = $this->createCustomerWithPoints(50);
        $itemId         = $this->createTestItem(HAS_NO_STOCK);

        $saleModel  = model(Sale::class);
        $saleStatus = SUSPENDED;
        $items      = $this->buildCartLine($itemId, 1, 60.00);
        $payments   = [
            0 => [
                'payment_type'    => lang('Sales.giftcard') . ':' . $giftcardNumber,
                'payment_amount'  => 40.00,
                'cash_refund'     => 0,
                'cash_adjustment' => 0,
                'reference_code'  => null,
            ],
            1 => [
                'payment_type'    => lang('Sales.rewards'),
                'payment_amount'  => 20.00,
                'cash_refund'     => 0,
                'cash_adjustment' => 0,
                'reference_code'  => null,
            ],
        ];
        $salesTaxes = [[], []];

        $suspendedSaleId = $saleModel->save_value(
            NEW_ENTRY,
            $saleStatus,
            $items,
            $customerId,
            $employeeId,
            'suspended sale',
            null,
            null,
            null,
            SALE_TYPE_POS,
            $payments,
            null,
            $salesTaxes
        );

        $this->assertGreaterThan(0, $suspendedSaleId);
        $this->assertEqualsWithDelta(100.00, $this->getGiftcardValue($giftcardNumber), 0.001);
        $this->assertSame(50, $this->getCustomerPoints($customerId));

        $saleStatus = COMPLETED;
        $completedSaleId = $saleModel->save_value(
            $suspendedSaleId,
            $saleStatus,
            $items,
            $customerId,
            $employeeId,
            'completed sale',
            null,
            null,
            null,
            SALE_TYPE_POS,
            $payments,
            null,
            $salesTaxes
        );

        $this->assertSame($suspendedSaleId, $completedSaleId);
        $this->assertEqualsWithDelta(60.00, $this->getGiftcardValue($giftcardNumber), 0.001);
        $this->assertSame(30, $this->getCustomerPoints($customerId));
    }

    public function testCheckWorkOrderNumberExists_MatchesWorkOrderNumberColumn(): void
    {
        $employeeId = $this->createEmployee();
        $itemId     = $this->createTestItem(HAS_NO_STOCK);

        $saleModel  = model(Sale::class);
        $saleStatus = SUSPENDED;
        $items      = $this->buildCartLine($itemId, 1, 10.00);
        $salesTaxes = [[], []];

        $saleId = $saleModel->save_value(
            NEW_ENTRY,
            $saleStatus,
            $items,
            NEW_ENTRY,
            $employeeId,
            'work order test',
            'INV-ONLY-999',
            'WO-REAL-123',
            null,
            SALE_TYPE_WORK_ORDER,
            [],
            null,
            $salesTaxes
        );

        $this->assertGreaterThan(0, $saleId);
        $this->assertTrue($saleModel->check_work_order_number_exists('WO-REAL-123'));
        $this->assertFalse($saleModel->check_work_order_number_exists('INV-ONLY-999'));
    }

    public function testDeleteSuspendedSale_HandlesNullDinnerTableWhenEnabled(): void
    {
        $ospos = config(OSPOS::class);
        $priorSetting = $ospos->settings['dinner_table_enable'] ?? false;
        $ospos->settings['dinner_table_enable'] = true;

        try {
            $employeeId = $this->createEmployee();
            $itemId     = $this->createTestItem(HAS_NO_STOCK);

            $saleModel  = model(Sale::class);
            $saleStatus = SUSPENDED;
            $items      = $this->buildCartLine($itemId, 1, 10.00);
            $salesTaxes = [[], []];

            $saleId = $saleModel->save_value(
                NEW_ENTRY,
                $saleStatus,
                $items,
                NEW_ENTRY,
                $employeeId,
                'null table test',
                null,
                null,
                null,
                SALE_TYPE_POS,
                [],
                null,
                $salesTaxes
            );

            $this->assertGreaterThan(0, $saleId);
            $this->assertTrue($saleModel->clear_suspended_sale_detail($saleId));
            $this->assertTrue($saleModel->delete_suspended_sale($saleId));
        } finally {
            $ospos->settings['dinner_table_enable'] = $priorSetting;
        }
    }

    public function testSaveValue_InsertsAndUpdatesRewardsRow(): void
    {
        $employeeId = $this->createEmployee();
        $itemId     = $this->createTestItem(HAS_NO_STOCK);
        $saleModel  = model(Sale::class);
        $saleStatus = COMPLETED;
        $items      = $this->buildCartLine($itemId, 1, 10.00);
        $salesTaxes = [[], []];

        $saleId = $saleModel->save_value(
            NEW_ENTRY,
            $saleStatus,
            $items,
            NEW_ENTRY,
            $employeeId,
            'rewards test',
            null,
            null,
            null,
            SALE_TYPE_POS,
            [],
            null,
            $salesTaxes
        );

        $rewardsModel = model(Rewards::class);
        $rewardsData  = [
            'sale_id' => $saleId,
            'earned'  => 10.0,
            'used'    => 0.0,
        ];

        $this->assertTrue($rewardsModel->save_value($rewardsData));
        $this->assertArrayHasKey('id', $rewardsData);
        $rewardsId = (int) $rewardsData['id'];
        $this->assertTrue($rewardsModel->exists($rewardsId));

        $rewardsData['used'] = 5.0;
        $this->assertTrue($rewardsModel->save_value($rewardsData, $rewardsId));
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

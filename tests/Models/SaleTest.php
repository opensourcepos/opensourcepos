<?php

namespace Tests\Models;

use App\Models\Sale;
use CodeIgniter\BaseModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;
use Config\OSPOS;
use ReflectionProperty;
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
    protected $namespace   = 'App';

    private const LOCATION_ID = 1;

    protected function setUp(): void
    {
        $this->resetDatabaseOnce();

        parent::setUp();

        config(OSPOS::class)->update_settings();
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

    public function testSaveValue_RejectsCompletingSaleThatIsNoLongerSuspended(): void
    {
        $employeeId = $this->createEmployee();
        $giftcardNumber = $this->createGiftcard(100.00);
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $saleModel = model(Sale::class);
        $saleStatus = SUSPENDED;
        $items = $this->buildCartLine($itemId, 1, 30.00);
        $salesTaxes = [[], []];
        $payments = [
            [
                'payment_type' => lang('Sales.giftcard') . ':' . $giftcardNumber,
                'payment_amount' => 30.00,
                'cash_refund' => 0,
                'cash_adjustment' => 0,
                'reference_code' => null,
            ],
        ];

        $saleId = $saleModel->save_value(
            NEW_ENTRY,
            $saleStatus,
            $items,
            NEW_ENTRY,
            $employeeId,
            'test suspended sale',
            null,
            null,
            null,
            SALE_TYPE_POS,
            [],
            null,
            $salesTaxes
        );

        $this->assertGreaterThan(0, $saleId);
        $this->assertEqualsWithDelta(100.00, $this->getGiftcardValue($giftcardNumber), 0.001);

        $saleStatus = COMPLETED;
        $result = $saleModel->save_value(
            $saleId,
            $saleStatus,
            $items,
            NEW_ENTRY,
            $employeeId,
            'test completed sale',
            null,
            null,
            null,
            SALE_TYPE_POS,
            $payments,
            null,
            $salesTaxes
        );

        $this->assertSame($saleId, $result);
        $balanceAfterCompletion = $this->getGiftcardValue($giftcardNumber);
        $this->assertEqualsWithDelta(70.00, $balanceAfterCompletion, 0.001);

        $db = Database::connect();
        $paymentColumns = 'sale_id, payment_type, payment_amount, cash_refund, cash_adjustment, employee_id, reference_code';
        $paymentsAfterCompletion = $db->table('sales_payments')->select($paymentColumns)->where('sale_id', $saleId)->orderBy('payment_id', 'ASC')->get()->getResultArray();
        $itemsAfterCompletion = $db->table('sales_items')->where('sale_id', $saleId)->orderBy('line', 'ASC')->get()->getResultArray();
        $statusAfterCompletion = (int) $db->table('sales')->where('sale_id', $saleId)->get()->getRow()->sale_status;
        $this->assertCount(1, $paymentsAfterCompletion);
        $this->assertCount(1, $itemsAfterCompletion);
        $this->assertSame(COMPLETED, $statusAfterCompletion);

        $result = $saleModel->save_value(
            $saleId,
            $saleStatus,
            $items,
            NEW_ENTRY,
            $employeeId,
            'test completed sale',
            null,
            null,
            null,
            SALE_TYPE_POS,
            $payments,
            null,
            $salesTaxes
        );

        $this->assertSame(-1, $result);
        $this->assertEqualsWithDelta($balanceAfterCompletion, $this->getGiftcardValue($giftcardNumber), 0.001);
        $this->assertSame($paymentsAfterCompletion, $db->table('sales_payments')->select($paymentColumns)->where('sale_id', $saleId)->orderBy('payment_id', 'ASC')->get()->getResultArray());
        $this->assertSame($itemsAfterCompletion, $db->table('sales_items')->where('sale_id', $saleId)->orderBy('line', 'ASC')->get()->getResultArray());
        $this->assertSame($statusAfterCompletion, (int) $db->table('sales')->where('sale_id', $saleId)->get()->getRow()->sale_status);
    }

    public function testSaveValue_AcceptsResuspendingSuspendedSale(): void
    {
        $employeeId = $this->createEmployee();
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $saleModel = model(Sale::class);
        $saleStatus = SUSPENDED;
        $items = $this->buildCartLine($itemId, 1, 30.00);
        $salesTaxes = [[], []];

        $saleId = $saleModel->save_value(
            NEW_ENTRY,
            $saleStatus,
            $items,
            NEW_ENTRY,
            $employeeId,
            'test suspended sale',
            null,
            null,
            null,
            SALE_TYPE_POS,
            [],
            null,
            $salesTaxes
        );

        $this->assertGreaterThan(0, $saleId);

        $items = $this->buildCartLine($itemId, 2.5, 12.00);
        $payments = [
            [
                'payment_type' => lang('Sales.cash'),
                'payment_amount' => 30.00,
                'cash_refund' => 0,
                'cash_adjustment' => 0,
                'reference_code' => null,
            ],
        ];

        $result = $saleModel->save_value(
            $saleId,
            $saleStatus,
            $items,
            NEW_ENTRY,
            $employeeId,
            'test suspended sale',
            null,
            null,
            null,
            SALE_TYPE_POS,
            $payments,
            null,
            $salesTaxes
        );

        $this->assertSame($saleId, $result);

        $db = Database::connect($this->DBGroup);
        $this->assertSame(SUSPENDED, (int) $db->table('sales')->where('sale_id', $saleId)->get()->getRow()->sale_status);
        $savedItems = $db->table('sales_items')->where('sale_id', $saleId)->get()->getResultArray();
        $savedPayments = $db->table('sales_payments')->where('sale_id', $saleId)->get()->getResultArray();
        $this->assertCount(1, $savedItems);
        $this->assertCount(1, $savedPayments);
        $this->assertSame($items[0]['item_id'], (int) $savedItems[0]['item_id']);
        $this->assertSame($items[0]['quantity'], (float) $savedItems[0]['quantity_purchased']);
        $this->assertSame($items[0]['price'], (float) $savedItems[0]['item_unit_price']);
        $this->assertSame($payments[0]['payment_type'], $savedPayments[0]['payment_type']);
        $this->assertSame($payments[0]['payment_amount'], (float) $savedPayments[0]['payment_amount']);
    }

    public function testSaveValue_ReturnsFailureWhenSuspendedSaleLockQueryFails(): void
    {
        $employeeId = $this->createEmployee();
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $saleModel = model(Sale::class);
        $saleStatus = SUSPENDED;
        $items = $this->buildCartLine($itemId, 1, 30.00);
        $salesTaxes = [[], []];
        $payments = [
            [
                'payment_type' => lang('Sales.cash'),
                'payment_amount' => 30.00,
                'cash_refund' => 0,
                'cash_adjustment' => 0,
                'reference_code' => null,
            ],
        ];

        $saleId = $saleModel->save_value(
            NEW_ENTRY,
            $saleStatus,
            $items,
            NEW_ENTRY,
            $employeeId,
            'test suspended sale',
            null,
            null,
            null,
            SALE_TYPE_POS,
            $payments,
            null,
            $salesTaxes
        );

        $this->assertGreaterThan(0, $saleId);

        $db = Database::connect($this->DBGroup);
        $itemsBeforeFailure = $db->table('sales_items')->where('sale_id', $saleId)->countAllResults();
        $paymentsBeforeFailure = $db->table('sales_payments')->where('sale_id', $saleId)->countAllResults();
        $this->assertSame(1, $itemsBeforeFailure);
        $this->assertSame(1, $paymentsBeforeFailure);

        $connection = $this->getMockBuilder($db::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['query'])
            ->getMock();
        $connection->connID = $db->connID;
        $connection->setPrefix($db->getPrefix());
        $connection->method('query')->willReturnCallback(
            static function (string $sql, mixed $binds = null, bool $setEscapeFlags = true, string $queryClass = '') use ($db, $connection): mixed {
                if (str_contains($sql, 'FOR UPDATE')) {
                    self::assertSame(1, $connection->transDepth);
                    (new ReflectionProperty(BaseConnection::class, 'transStatus'))->setValue($connection, false);

                    return false;
                }

                return $db->query($sql, $binds, $setEscapeFlags, $queryClass);
            }
        );

        $saleModel = new Sale();
        (new ReflectionProperty(BaseModel::class, 'db'))->setValue($saleModel, $connection);
        $saleStatus = COMPLETED;

        try {
            $result = $saleModel->save_value(
                $saleId,
                $saleStatus,
                $items,
                NEW_ENTRY,
                $employeeId,
                'test completed sale',
                null,
                null,
                null,
                SALE_TYPE_POS,
                $payments,
                null,
                $salesTaxes
            );

            $this->assertSame(-1, $result);
            $this->assertSame(0, $connection->transDepth);
            $this->assertSame(SUSPENDED, (int) $db->table('sales')->where('sale_id', $saleId)->get()->getRow()->sale_status);
            $this->assertSame($itemsBeforeFailure, $db->table('sales_items')->where('sale_id', $saleId)->countAllResults());
            $this->assertSame($paymentsBeforeFailure, $db->table('sales_payments')->where('sale_id', $saleId)->countAllResults());
        } finally {
            $connection->transRollback();
        }
    }

    public function testSaveValue_RejectsUnknownSaleId(): void
    {
        $employeeId = $this->createEmployee();
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $saleModel = model(Sale::class);
        $saleStatus = COMPLETED;
        $items = $this->buildCartLine($itemId, 1, 30.00);
        $salesTaxes = [[], []];
        $payments = [
            [
                'payment_type' => lang('Sales.cash'),
                'payment_amount' => 30.00,
                'cash_refund' => 0,
                'cash_adjustment' => 0,
                'reference_code' => null,
            ],
        ];
        $db = Database::connect($this->DBGroup);
        $unknownSaleId = (int) $db->table('sales')->selectMax('sale_id')->get()->getRow()->sale_id + 1;
        $salesBefore = $this->countSalesRows();
        $itemsBefore = $db->table('sales_items')->countAllResults();
        $paymentsBefore = $db->table('sales_payments')->countAllResults();

        $result = $saleModel->save_value(
            $unknownSaleId,
            $saleStatus,
            $items,
            NEW_ENTRY,
            $employeeId,
            'test unknown sale',
            null,
            null,
            null,
            SALE_TYPE_POS,
            $payments,
            null,
            $salesTaxes
        );

        $this->assertSame(-1, $result);
        $this->assertSame($salesBefore, $this->countSalesRows());
        $this->assertSame($itemsBefore, $db->table('sales_items')->countAllResults());
        $this->assertSame($paymentsBefore, $db->table('sales_payments')->countAllResults());
        $this->assertSame(0, $db->transDepth);
    }

    public function testSaveValue_CreditsGiftcardOnceWhenSuspendedReturnIsCompletedFromTwoConnections(): void
    {
        $employeeId = $this->createEmployee();
        $giftcardNumber = $this->createGiftcard(50.00);
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $saleModel = model(Sale::class);
        $saleStatus = SUSPENDED;
        $items = $this->buildCartLine($itemId, -1, 30.00);
        $salesTaxes = [[], []];
        $payments = [
            [
                'payment_type' => lang('Sales.giftcard') . ':' . $giftcardNumber,
                'payment_amount' => -30.00,
                'cash_refund' => 0,
                'cash_adjustment' => 0,
                'reference_code' => null,
            ],
        ];

        $saleId = $saleModel->save_value(
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

        $this->assertGreaterThan(0, $saleId);
        $this->assertEqualsWithDelta(50.00, $this->getGiftcardValue($giftcardNumber), 0.001);

        $db = Database::connect($this->DBGroup);
        $otherConnection = Database::connect($this->DBGroup, false);

        try {
            $otherSaleModel = new Sale();
            (new ReflectionProperty(BaseModel::class, 'db'))->setValue($otherSaleModel, $otherConnection);
            $saleStatus = COMPLETED;

            $result = $otherSaleModel->save_value(
                $saleId,
                $saleStatus,
                $items,
                NEW_ENTRY,
                $employeeId,
                'test completed return',
                null,
                null,
                null,
                SALE_TYPE_RETURN,
                $payments,
                null,
                $salesTaxes
            );

            $this->assertSame($saleId, $result);
            $this->assertSame(0, $otherConnection->transDepth);

            $result = $saleModel->save_value(
                $saleId,
                $saleStatus,
                $items,
                NEW_ENTRY,
                $employeeId,
                'test duplicate completed return',
                null,
                null,
                null,
                SALE_TYPE_RETURN,
                $payments,
                null,
                $salesTaxes
            );

            $this->assertSame(-1, $result);
            $this->assertSame(0, $db->transDepth);
            $this->assertEqualsWithDelta(80.00, $this->getGiftcardValue($giftcardNumber), 0.001);
            $this->assertSame(COMPLETED, (int) $db->table('sales')->where('sale_id', $saleId)->get()->getRow()->sale_status);
            $this->assertSame(1, $db->table('sales_payments')->where('sale_id', $saleId)->countAllResults());
        } finally {
            $otherConnection->transRollback();
            $otherConnection->close();
        }
    }

    public function testSaveValue_RejectsCompletionWhileAnotherConnectionHoldsSaleLock(): void
    {
        $employeeId = $this->createEmployee();
        $giftcardNumber = $this->createGiftcard(50.00);
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $saleModel = model(Sale::class);
        $saleStatus = SUSPENDED;
        $items = $this->buildCartLine($itemId, -1, 30.00);
        $salesTaxes = [[], []];
        $payments = [
            [
                'payment_type' => lang('Sales.giftcard') . ':' . $giftcardNumber,
                'payment_amount' => -30.00,
                'cash_refund' => 0,
                'cash_adjustment' => 0,
                'reference_code' => null,
            ],
        ];

        $saleId = $saleModel->save_value(
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

        $this->assertGreaterThan(0, $saleId);

        $db = Database::connect($this->DBGroup);
        $lockWaitTimeout = (int) $db->query('SELECT @@SESSION.innodb_lock_wait_timeout AS lock_wait_timeout')->getRow()->lock_wait_timeout;
        $otherConnection = Database::connect($this->DBGroup, false);

        try {
            $this->assertTrue($otherConnection->transBegin());
            $lockedSale = $otherConnection->query(
                'SELECT sale_status FROM ' . $otherConnection->prefixTable('sales') . ' WHERE sale_id = ? FOR UPDATE',
                [$saleId]
            )->getRow();
            $this->assertSame(SUSPENDED, (int) $lockedSale->sale_status);
            $db->query('SET SESSION innodb_lock_wait_timeout = 1');
            $saleStatus = COMPLETED;

            $result = $saleModel->save_value(
                $saleId,
                $saleStatus,
                $items,
                NEW_ENTRY,
                $employeeId,
                'test locked return',
                null,
                null,
                null,
                SALE_TYPE_RETURN,
                $payments,
                null,
                $salesTaxes
            );

            $this->assertSame(-1, $result);
            $this->assertStringContainsString('FOR UPDATE', (string) $db->getLastQuery());
            $this->assertSame(0, $db->transDepth);
            $this->assertTrue($otherConnection->transRollback());
            $this->assertEqualsWithDelta(50.00, $this->getGiftcardValue($giftcardNumber), 0.001);
            $this->assertSame(SUSPENDED, (int) $db->table('sales')->where('sale_id', $saleId)->get()->getRow()->sale_status);
        } finally {
            $otherConnection->transRollback();
            $otherConnection->close();
            $db->transRollback();
            $db->resetTransStatus();
            $db->query('SET SESSION innodb_lock_wait_timeout = ?', [$lockWaitTimeout]);
        }
    }

    public function testSaveValue_CommitsValidSaleOnSameConnectionAfterRejectedSave(): void
    {
        $employeeId = $this->createEmployee();
        $itemId = $this->createTestItem(HAS_NO_STOCK);
        $saleModel = model(Sale::class);
        $saleStatus = COMPLETED;
        $items = $this->buildCartLine($itemId, 1, 30.00);
        $salesTaxes = [[], []];
        $payments = [
            [
                'payment_type' => lang('Sales.cash'),
                'payment_amount' => 30.00,
                'cash_refund' => 0,
                'cash_adjustment' => 0,
                'reference_code' => null,
            ],
        ];

        $saleId = $saleModel->save_value(
            NEW_ENTRY,
            $saleStatus,
            $items,
            NEW_ENTRY,
            $employeeId,
            'test completed sale',
            null,
            null,
            null,
            SALE_TYPE_POS,
            $payments,
            null,
            $salesTaxes
        );

        $this->assertGreaterThan(0, $saleId);

        $result = $saleModel->save_value(
            $saleId,
            $saleStatus,
            $items,
            NEW_ENTRY,
            $employeeId,
            'test rejected sale',
            null,
            null,
            null,
            SALE_TYPE_POS,
            $payments,
            null,
            $salesTaxes
        );

        $this->assertSame(-1, $result);

        $db = Database::connect($this->DBGroup);
        $this->assertSame(0, $db->transDepth);
        $newSaleId = $saleModel->save_value(
            NEW_ENTRY,
            $saleStatus,
            $items,
            NEW_ENTRY,
            $employeeId,
            'test sale after rejected save',
            null,
            null,
            null,
            SALE_TYPE_POS,
            $payments,
            null,
            $salesTaxes
        );

        $this->assertGreaterThan(0, $newSaleId);
        $this->assertNotSame($saleId, $newSaleId);
        $this->assertSame(0, $db->transDepth);
        $otherConnection = Database::connect($this->DBGroup, false);

        try {
            $committedSale = $otherConnection->table('sales')->where('sale_id', $newSaleId)->get()->getRowArray();
            $this->assertNotNull($committedSale);
            $this->assertSame($newSaleId, (int) $committedSale['sale_id']);
            $this->assertSame(COMPLETED, (int) $committedSale['sale_status']);
        } finally {
            $otherConnection->transRollback();
            $otherConnection->close();
        }
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
}

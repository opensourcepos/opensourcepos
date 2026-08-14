<?php

namespace Tests\Models;

use App\Models\Item;
use App\Models\Receiving;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Regression tests for GHSA-995p-52qw-5hh2: Receiving::delete_value() must
 * correctly reverse the stock quantity change it applied via
 * Item_quantity::changeQuantity(), using the same atomic upsert as the
 * sale-checkout and sale-cancel paths.
 */
class ReceivingTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = true;
    protected $namespace   = null;

    private const LOCATION_ID = 1;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function createEmployee(): int
    {
        $db = \Config\Database::connect();

        $db->table('people')->insert([
            'first_name'   => 'Test',
            'last_name'    => 'Employee',
            'phone_number' => '555-0200',
            'email'        => 'employee-' . uniqid() . '@test.com',
            'address_1'    => '',
            'address_2'    => '',
            'city'         => '',
            'state'        => '',
            'zip'          => '',
            'country'      => '',
            'comments'     => '',
        ]);
        $personId = (int) $db->insertID();

        $db->table('employees')->insert([
            'username'  => 'employee_' . uniqid(),
            'password'  => password_hash('password123', PASSWORD_DEFAULT),
            'person_id' => $personId,
        ]);

        return $personId;
    }

    protected function createSupplier(): int
    {
        $db = \Config\Database::connect();

        $db->table('people')->insert([
            'first_name'   => 'Test',
            'last_name'    => 'Supplier',
            'phone_number' => '555-0400',
            'email'        => 'supplier-' . uniqid() . '@test.com',
            'address_1'    => '',
            'address_2'    => '',
            'city'         => '',
            'state'        => '',
            'zip'          => '',
            'country'      => '',
            'comments'     => '',
        ]);
        $personId = (int) $db->insertID();

        $db->table('suppliers')->insert([
            'person_id' => $personId,
            'deleted'   => 0,
        ]);

        return $personId;
    }

    protected function createTestItem(int $stockType = HAS_STOCK): int
    {
        $itemData = [
            'item_id'               => null,
            'name'                  => 'Test Item',
            'description'           => 'Test Item',
            'category'              => 'Test Category',
            'cost_price'            => 1.00,
            'unit_price'            => 5.00,
            'reorder_level'         => 0,
            'item_number'           => 'TEST-' . uniqid(),
            'allow_alt_description' => 0,
            'is_serialized'         => 0,
            'stock_type'            => $stockType,
            'deleted'               => 0,
        ];

        $itemModel = model(Item::class);
        $itemModel->save_value($itemData);

        return (int) $itemData['item_id'];
    }

    protected function buildReceivingLine(int $itemId, float $quantity, float $costPrice): array
    {
        return [
            0 => [
                'item_id'            => $itemId,
                'line'               => 0,
                'description'        => 'Test Item',
                'serialnumber'       => '',
                'quantity'           => $quantity,
                'receiving_quantity' => 1,
                'discount'           => 0,
                'discount_type'      => 0,
                'price'              => $costPrice,
                'item_location'      => self::LOCATION_ID,
            ],
        ];
    }

    protected function getItemQuantity(int $itemId): float
    {
        $row = \Config\Database::connect()->table('item_quantities')
            ->where('item_id', $itemId)
            ->where('location_id', self::LOCATION_ID)
            ->get()
            ->getRow();

        return $row === null ? 0.0 : (float) $row->quantity;
    }

    public function testDeleteValueRestoresStockAfterReceiving(): void
    {
        $employeeId = $this->createEmployee();
        $supplierId = $this->createSupplier();
        $itemId     = $this->createTestItem(HAS_STOCK);

        // Deliberately no pre-existing item_quantities row for this item/location.

        $receivingModel = model(Receiving::class);
        $items          = $this->buildReceivingLine($itemId, 5, 1.00);

        $receivingId = $receivingModel->save_value(
            $items,
            $supplierId,
            $employeeId,
            'test receiving',
            'REF-' . uniqid(),
            'Cash'
        );

        $this->assertGreaterThan(0, $receivingId);
        $this->assertEqualsWithDelta(5.0, $this->getItemQuantity($itemId), 0.001);

        $deleteResult = $receivingModel->delete_value($receivingId, $employeeId, true);

        $this->assertTrue($deleteResult);
        $this->assertEqualsWithDelta(0.0, $this->getItemQuantity($itemId), 0.001);
    }
}

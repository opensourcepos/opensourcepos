<?php

namespace App\Commands;

use App\Models\Item;
use App\Models\Item_lot;
use App\Models\Item_quantity;
use App\Models\Receiving;
use App\Models\Sale;
use App\Models\Supplier;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use RuntimeException;
use Throwable;

/**
 * @internal
 */
final class LotTrackingSmokeTest extends BaseCommand
{
    protected $group       = 'Tests';
    protected $name        = 'test:lottracking';
    protected $description = 'Runs an end-to-end smoke test of the supplier lot tracking feature. All test data is rolled back.';
    private int $passes    = 0;
    private int $failures  = 0;

    public function run(array $params): void
    {
        $db = Database::connect();
        $db->transBegin();

        try {
            $item_id   = $this->createItem();
            $supplierA = $this->createSupplier('Alpha Corp');
            $supplierB = $this->createSupplier('Bravo Corp');
            $supplierC = $this->createSupplier('Charlie Corp');

            $receivingA = $this->receive($item_id, $supplierA, 5);
            $receivingB = $this->receive($item_id, $supplierB, 3);
            $receivingC = $this->receive($item_id, $supplierC, 2);

            $this->assertLot('Lot A after receiving 5', $item_id, $receivingA, 5);
            $this->assertLot('Lot B after receiving 3', $item_id, $receivingB, 3);
            $this->assertLot('Lot C after receiving 2', $item_id, $receivingC, 2);
            $this->assertStock('Stock quantity after receiving', $item_id, 10);

            $sale1 = $this->sell($item_id, 4);

            $this->assertLot('Lot A after selling 4 (FIFO)', $item_id, $receivingA, 1);
            $this->assertLot('Lot B unchanged after selling 4', $item_id, $receivingB, 3);
            $this->assertLot('Lot C unchanged after selling 4', $item_id, $receivingC, 2);
            $this->assertAllocation('Sale 1 allocated to lot A', $sale1, $receivingA, 4);
            $this->assertSupplierName('Report supplier for sale 1', $sale1, 'Alpha Corp');

            $sale2 = $this->sell($item_id, 6);

            $this->assertLot('Lot A after selling 6', $item_id, $receivingA, 0);
            $this->assertLot('Lot B after selling 6', $item_id, $receivingB, 0);
            $this->assertLot('Lot C after selling 6', $item_id, $receivingC, 0);
            $this->assertStock('Stock quantity after selling all', $item_id, 0);
            $this->assertAllocation('Sale 2 part from lot A', $sale2, $receivingA, 1);
            $this->assertAllocation('Sale 2 part from lot B', $sale2, $receivingB, 3);
            $this->assertAllocation('Sale 2 part from lot C', $sale2, $receivingC, 2);

            $this->voidSale($sale2);

            $this->assertLot('Lot A after voiding sale 2', $item_id, $receivingA, 1);
            $this->assertLot('Lot B after voiding sale 2', $item_id, $receivingB, 3);
            $this->assertLot('Lot C after voiding sale 2', $item_id, $receivingC, 2);
            $this->assertNoAllocations('Allocations removed after voiding sale 2', $sale2);

            $this->voidSale($sale1);

            $this->assertLot('Lot A after voiding sale 1', $item_id, $receivingA, 5);
            $this->assertLot('Lot B after voiding sale 1', $item_id, $receivingB, 3);
            $this->assertLot('Lot C after voiding sale 1', $item_id, $receivingC, 2);
            $this->assertStock('Stock quantity after voiding all sales', $item_id, 10);

            $sale3 = $this->sell($item_id, 2);
            $this->assertLot('Lot A after selling 2', $item_id, $receivingA, 3);

            $sale4 = $this->returnSale($item_id, 2);

            $this->assertLot('Unknown lot after returning 2', $item_id, 0, 2);
            $this->assertAllocation('Return sale allocated to unknown lot', $sale4, 0, 2);
            $this->assertStock('Stock quantity after sale and return', $item_id, 10);

            // Suspended sale reservation tests
            $suspendedSale = $this->suspend($item_id, 3);
            $this->assertStock('Stock quantity after suspending 3', $item_id, 7);
            $this->assertReservation('Reservation recorded for suspended sale', $suspendedSale, $item_id, 3);

            // Completing the suspended sale releases the reservation and re-applies the sale decrement
            $this->completeSuspended($suspendedSale, $item_id, 3);
            $this->assertStock('Stock quantity after completing suspended sale', $item_id, 7);
            $this->assertNoReservations('Reservations cleared after completing', $suspendedSale);
            $this->assertAllocation('Completed sale consumed from lot A', $suspendedSale, $receivingA, 3);

            $sale5 = $this->suspend($item_id, 2);
            $this->assertStock('Stock quantity after suspending 2', $item_id, 5);
            $this->voidSale($sale5);
            $this->assertStock('Stock quantity after cancelling suspended sale', $item_id, 7);
            $this->assertNoReservations('Reservations cleared after cancelling', $sale5);
        } catch (Throwable $e) {
            $this->fail('Exception: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
        }

        if ($db->transStatus()) {
            $db->transRollback();
        }

        CLI::newLine();
        CLI::write('Lot tracking smoke test: ' . $this->passes . ' passed, ' . $this->failures . ' failed.', $this->failures > 0 ? 'red' : 'green');

        exit($this->failures > 0 ? 1 : 0);
    }

    private function createItem(): int
    {
        $item      = model(Item::class);
        $item_data = [
            'name'                  => 'Lot Test Widget',
            'category'              => 'Lot Test',
            'description'           => 'Smoke test item',
            'cost_price'            => 10,
            'unit_price'            => 15,
            'reorder_level'         => 0,
            'receiving_quantity'    => 1,
            'allow_alt_description' => 0,
            'is_serialized'         => 0,
            'deleted'               => 0,
            'stock_type'            => HAS_STOCK,
            'item_type'             => ITEM,
            'qty_per_pack'          => 1,
            'pack_name'             => 'Each',
            'hsn_code'              => '',
        ];

        if (! $item->save_value($item_data)) {
            $this->fail('Could not create item');
        }

        return $item_data['item_id'];
    }

    private function createSupplier(string $company): int
    {
        $supplier    = model(Supplier::class);
        $person_data = [
            'first_name'   => $company . ' First',
            'last_name'    => $company . ' Last',
            'gender'       => 0,
            'phone_number' => '555-1000',
            'email'        => strtolower(str_replace(' ', '', $company)) . '@test.com',
            'address_1'    => '1 Test St',
            'address_2'    => '',
            'city'         => 'Testville',
            'state'        => 'TS',
            'zip'          => '12345',
            'country'      => 'Testland',
            'comments'     => 'created by lot tracking smoke test',
        ];
        $supplier_data = [
            'company_name'   => $company,
            'agency_name'    => '',
            'account_number' => uniqid('acc_'),
            'tax_id'         => '',
            'deleted'        => 0,
            'category'       => 0,
        ];

        if (! $supplier->save_supplier($person_data, $supplier_data)) {
            $this->fail('Could not create supplier ' . $company);
        }

        return $supplier_data['person_id'];
    }

    private function receive(int $item_id, int $supplier_id, float $quantity): int
    {
        $receiving = model(Receiving::class);
        $cart      = [
            1 => [
                'item_id'            => $item_id,
                'item_location'      => 1,
                'line'               => 1,
                'description'        => '',
                'serialnumber'       => '',
                'quantity'           => $quantity,
                'receiving_quantity' => 1,
                'discount'           => 0,
                'discount_type'      => PERCENT,
                'price'              => 10,
            ],
        ];

        return $receiving->save_value($cart, $supplier_id, 1, 'lot test receiving', '', null);
    }

    private function sell(int $item_id, float $quantity): int
    {
        $sale  = model(Sale::class);
        $items = [
            1 => [
                'item_id'       => $item_id,
                'item_location' => 1,
                'line'          => 1,
                'description'   => '',
                'serialnumber'  => '',
                'quantity'      => $quantity,
                'discount'      => 0,
                'discount_type' => PERCENT,
                'cost_price'    => 10,
                'price'         => 15,
                'print_option'  => PRINT_YES,
            ],
        ];
        $status      = (string) COMPLETED;
        $sales_taxes = [[], []];

        return $sale->save_value(NEW_ENTRY, $status, $items, NEW_ENTRY, 1, 'lot test sale', null, null, null, SALE_TYPE_POS, [], null, $sales_taxes);
    }

    private function returnSale(int $item_id, float $quantity): int
    {
        return $this->sell($item_id, -$quantity);
    }

    private function suspend(int $item_id, float $quantity): int
    {
        $sale  = model(Sale::class);
        $items = [
            [
                'item_id'       => $item_id,
                'item_location' => 1,
                'line'          => 1,
                'description'   => '',
                'serialnumber'  => '',
                'quantity'      => $quantity,
                'discount'      => 0,
                'discount_type' => PERCENT,
                'cost_price'    => 10,
                'price'         => 15,
                'print_option'  => PRINT_YES,
            ],
        ];
        $status      = (string) SUSPENDED;
        $sales_taxes = [[], []];

        return $sale->save_value(NEW_ENTRY, $status, $items, NEW_ENTRY, 1, 'lot test suspend', null, null, null, SALE_TYPE_POS, [], null, $sales_taxes);
    }

    private function completeSuspended(int $sale_id, int $item_id, float $quantity): void
    {
        $sale  = model(Sale::class);
        $items = [
            [
                'item_id'       => $item_id,
                'item_location' => 1,
                'line'          => 1,
                'description'   => '',
                'serialnumber'  => '',
                'quantity'      => $quantity,
                'discount'      => 0,
                'discount_type' => PERCENT,
                'cost_price'    => 10,
                'price'         => 15,
                'print_option'  => PRINT_YES,
            ],
        ];
        $status      = (string) COMPLETED;
        $sales_taxes = [[], []];

        $sale->save_value($sale_id, $status, $items, NEW_ENTRY, 1, 'lot test complete suspended', null, null, null, SALE_TYPE_POS, [], null, $sales_taxes);
    }

    private function voidSale(int $sale_id): void
    {
        $sale = model(Sale::class);
        $sale->delete($sale_id, false, true, 1);
    }

    private function assertLot(string $label, int $item_id, int $receiving_id, float $expected): void
    {
        $item_lot = model(Item_lot::class);
        $builder  = Database::connect()->table('item_lots');
        $builder->where('item_id', $item_id);
        $builder->where('receiving_id', $receiving_id);
        $row = $builder->get()->getRow();

        $actual = $row !== null ? (float) $row->quantity : 0.0;
        if (abs($actual - $expected) > 0.001) {
            $this->fail("{$label}: expected {$expected}, got {$actual}");
        } else {
            $this->pass("{$label}: {$actual}");
        }
    }

    private function assertStock(string $label, int $item_id, float $expected): void
    {
        $item_quantity = model(Item_quantity::class);
        $row           = $item_quantity->get_item_quantity($item_id, 1);
        $actual        = (float) $row->quantity;
        if (abs($actual - $expected) > 0.001) {
            $this->fail("{$label}: expected {$expected}, got {$actual}");
        } else {
            $this->pass("{$label}: {$actual}");
        }
    }

    private function assertReservation(string $label, int $sale_id, int $item_id, float $expected): void
    {
        $builder = Database::connect()->table('suspended_sales_reservations');
        $builder->where('sale_id', $sale_id);
        $builder->where('item_id', $item_id);
        $row = $builder->get()->getRow();

        $actual = $row !== null ? (float) $row->quantity_reserved : 0.0;
        if (abs($actual - $expected) > 0.001) {
            $this->fail("{$label}: expected {$expected}, got {$actual}");
        } else {
            $this->pass("{$label}: {$actual}");
        }
    }

    private function assertNoReservations(string $label, int $sale_id): void
    {
        $builder = Database::connect()->table('suspended_sales_reservations');
        $builder->where('sale_id', $sale_id);
        $count = $builder->countAllResults();
        if ($count !== 0) {
            $this->fail("{$label}: found {$count} reservation rows");
        } else {
            $this->pass($label);
        }
    }

    private function assertAllocation(string $label, int $sale_id, int $receiving_id, float $expected): void
    {
        $builder = Database::connect()->table('sales_items_lots');
        $builder->where('sale_id', $sale_id);
        $builder->where('receiving_id', $receiving_id);
        $row = $builder->get()->getRow();

        $actual = $row !== null ? (float) $row->quantity : 0.0;
        if (abs($actual - $expected) > 0.001) {
            $this->fail("{$label}: expected {$expected}, got {$actual}");
        } else {
            $this->pass("{$label}: {$actual}");
        }
    }

    private function assertNoAllocations(string $label, int $sale_id): void
    {
        $builder = Database::connect()->table('sales_items_lots');
        $builder->where('sale_id', $sale_id);
        $count = $builder->countAllResults();
        if ($count !== 0) {
            $this->fail("{$label}: found {$count} allocation rows");
        } else {
            $this->pass($label);
        }
    }

    private function assertSupplierName(string $label, int $sale_id, string $expected): void
    {
        $sale = model(Sale::class);
        $sale->create_temp_table(['sale_id' => $sale_id]);

        $db    = Database::connect();
        $error = $db->error();
        if (! empty($error['code'])) {
            throw new RuntimeException('create_temp_table: ' . $error['message']);
        }

        $builder = $db->table('sales_items_temp');
        $builder->where('sale_id', $sale_id);
        $result = $builder->get();
        if ($result === false) {
            throw new RuntimeException(Database::connect()->error()['message'] ?? 'temp table query failed');
        }
        $row = $result->getRow();

        $actual = $row->supplier_name ?? '';
        if ($actual !== $expected) {
            $this->fail("{$label}: expected '{$expected}', got '{$actual}'");
        } else {
            $this->pass("{$label}: {$actual}");
        }
    }

    private function pass(string $message): void
    {
        $this->passes++;
        CLI::write('[PASS] ' . $message, 'green');
    }

    private function fail(string $message): void
    {
        $this->failures++;
        CLI::write('[FAIL] ' . $message, 'red');
    }
}

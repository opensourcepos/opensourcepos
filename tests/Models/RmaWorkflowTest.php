<?php

namespace Tests\Models;

use App\Models\Item;
use App\Models\Item_quantity;
use App\Models\Rma;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class RmaWorkflowTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = true;
    protected $namespace;
    private int $item_id;
    private int $location_id;

    protected function setUp(): void
    {
        parent::setUp();

        $this->location_id = 1;

        $item      = model(Item::class);
        $item_data = [
            'name'                  => 'RMA Test Item',
            'item_number'           => 'RMA-TEST-' . uniqid(),
            'category'              => 'test',
            'supplier_id'           => null,
            'item_type'             => 0,
            'cost_price'            => 1.00,
            'unit_price'            => 2.00,
            'description'           => 'temp',
            'allow_alt_description' => 0,
            'is_serialized'         => 0,
            'deleted'               => 0,
        ];
        $result = $item->save_value($item_data);
        $this->assertTrue($result);
        $this->item_id = (int) $item_data['item_id'];

        $item_quantity = model(Item_quantity::class);
        $item_quantity->save_value(['item_id' => $this->item_id, 'location_id' => $this->location_id, 'quantity' => 10], $this->item_id, $this->location_id);
    }

    public function testStockUnitRmaDeductsThenAddsBackOnReplacement(): void
    {
        $item_quantity = model(Item_quantity::class);
        $rma           = model(Rma::class);

        $cart = [
            1 => [
                'item_id'     => $this->item_id,
                'line'        => 1,
                'quantity'    => 3,
                'description' => 'unit test rma',
            ],
        ];

        // Stock unit: deduct 3 on creation (returned to supplier)
        $rma_id = $rma->save_value($cart, 1, Rma::TYPE_STOCK, $this->location_id, null, null, null, 'stock rma');
        $this->assertNotSame(-1, $rma_id);
        $this->assertSame(7, (int) $item_quantity->get_item_quantity($this->item_id, $this->location_id)->quantity);

        // Resolve as replacement -> add 3 back
        $this->assertTrue($rma->resolve($rma_id, Rma::RESOLUTION_REPLACEMENT, 1));

        $info = $rma->get_info($rma_id)->getRowArray();
        $this->assertSame(Rma::RESOLUTION_REPLACEMENT, $info['resolution']);
        $this->assertSame(10, (int) $item_quantity->get_item_quantity($this->item_id, $this->location_id)->quantity);
    }

    public function testStockUnitRmaCreditMemoDoesNotAddBack(): void
    {
        $item_quantity = model(Item_quantity::class);
        $rma           = model(Rma::class);

        $cart = [
            1 => [
                'item_id'     => $this->item_id,
                'line'        => 1,
                'quantity'    => 3,
                'description' => 'unit test rma',
            ],
        ];

        $rma_id = $rma->save_value($cart, 1, Rma::TYPE_STOCK, $this->location_id, null, null, null, 'stock rma');
        $this->assertNotSame(-1, $rma_id);
        $this->assertSame(7, (int) $item_quantity->get_item_quantity($this->item_id, $this->location_id)->quantity);

        $this->assertTrue($rma->resolve($rma_id, Rma::RESOLUTION_CREDIT_MEMO, 1));

        // Credit memo: quantity stays deducted
        $this->assertSame(7, (int) $item_quantity->get_item_quantity($this->item_id, $this->location_id)->quantity);
    }

    public function testStockUnitRmaRepairAddsBack(): void
    {
        $item_quantity = model(Item_quantity::class);
        $rma           = model(Rma::class);

        $cart = [
            1 => [
                'item_id'  => $this->item_id,
                'line'     => 1,
                'quantity' => 2,
            ],
        ];

        $rma_id = $rma->save_value($cart, 1, Rma::TYPE_STOCK, $this->location_id, null, null, null, 'stock rma');
        $this->assertSame(8, (int) $item_quantity->get_item_quantity($this->item_id, $this->location_id)->quantity);

        $this->assertTrue($rma->resolve($rma_id, Rma::RESOLUTION_REPAIR, 1));
        $this->assertSame(10, (int) $item_quantity->get_item_quantity($this->item_id, $this->location_id)->quantity);
    }

    public function testClientUnitRmaNeverChangesQuantity(): void
    {
        $item_quantity = model(Item_quantity::class);
        $rma           = model(Rma::class);

        $cart = [
            1 => [
                'item_id'  => $this->item_id,
                'line'     => 1,
                'quantity' => 2,
            ],
        ];

        // Client unit: no deduction on creation
        $rma_id = $rma->save_value($cart, 1, Rma::TYPE_CLIENT, $this->location_id, null, 1, null, 'client rma');
        $this->assertNotSame(-1, $rma_id);
        $this->assertSame(10, (int) $item_quantity->get_item_quantity($this->item_id, $this->location_id)->quantity);

        // Resolve as replacement: still no change
        $this->assertTrue($rma->resolve($rma_id, Rma::RESOLUTION_REPLACEMENT, 1));
        $this->assertSame(10, (int) $item_quantity->get_item_quantity($this->item_id, $this->location_id)->quantity);
    }

    public function testVoidWarrantyStockUnitDoesNotAddBack(): void
    {
        $item_quantity = model(Item_quantity::class);
        $rma           = model(Rma::class);

        $cart = [
            1 => [
                'item_id'  => $this->item_id,
                'line'     => 1,
                'quantity' => 4,
            ],
        ];

        $rma_id = $rma->save_value($cart, 1, Rma::TYPE_STOCK, $this->location_id, null, null, null, 'stock rma');
        $this->assertSame(6, (int) $item_quantity->get_item_quantity($this->item_id, $this->location_id)->quantity);

        $this->assertTrue($rma->resolve($rma_id, Rma::RESOLUTION_VOID_WARRANTY, 1));
        $this->assertSame(6, (int) $item_quantity->get_item_quantity($this->item_id, $this->location_id)->quantity);
    }

    public function testClientUnitRmaStoresSourceSaleId(): void
    {
        $rma = model(Rma::class);

        $cart = [
            1 => [
                'item_id'  => $this->item_id,
                'line'     => 1,
                'quantity' => 2,
            ],
        ];

        // Client unit created from a completed sale (POS 70 equivalent)
        $rma_id = $rma->save_value($cart, 1, Rma::TYPE_CLIENT, $this->location_id, null, 1, 70, 'sale linked client rma');
        $this->assertNotSame(-1, $rma_id);

        $info = $rma->get_info($rma_id)->getRowArray();
        $this->assertSame(70, (int) $info['sale_id']);
        $this->assertSame(Rma::TYPE_CLIENT, (int) $info['rma_type']);
    }

    public function testStockUnitRmaDoesNotStoreSaleId(): void
    {
        $rma = model(Rma::class);

        $cart = [
            1 => [
                'item_id'  => $this->item_id,
                'line'     => 1,
                'quantity' => 2,
            ],
        ];

        $rma_id = $rma->save_value($cart, 1, Rma::TYPE_STOCK, $this->location_id, null, null, null, 'stock rma');
        $this->assertNotSame(-1, $rma_id);

        $info = $rma->get_info($rma_id)->getRowArray();
        $this->assertNull($info['sale_id']);
        $this->assertSame(Rma::TYPE_STOCK, (int) $info['rma_type']);
    }

    public function testIssueAndSerialNumberAreStoredPerItem(): void
    {
        $rma = model(Rma::class);

        $cart = [
            1 => [
                'item_id'       => $this->item_id,
                'line'          => 1,
                'quantity'      => 2,
                'description'   => 'returns',
                'issue'         => 'Screen flickers when powered on',
                'serial_number' => 'SN-12345',
            ],
        ];

        $rma_id = $rma->save_value($cart, 1, Rma::TYPE_CLIENT, $this->location_id, null, 1, 70, 'client rma');
        $this->assertNotSame(-1, $rma_id);

        $items = $rma->get_rma_items($rma_id)->getResultArray();
        $this->assertNotEmpty($items);
        $this->assertSame('Screen flickers when powered on', $items[0]['issue']);
        $this->assertSame('SN-12345', $items[0]['serial_number']);
    }

    public function testIssueAndSerialNumberDefaultToEmpty(): void
    {
        $rma = model(Rma::class);

        $cart = [
            1 => [
                'item_id'  => $this->item_id,
                'line'     => 1,
                'quantity' => 2,
            ],
        ];

        $rma_id = $rma->save_value($cart, 1, Rma::TYPE_STOCK, $this->location_id, null, null, null, 'stock rma');
        $this->assertNotSame(-1, $rma_id);

        $items = $rma->get_rma_items($rma_id)->getResultArray();
        $this->assertNotEmpty($items);
        $this->assertSame('', $items[0]['issue']);
        $this->assertSame('', $items[0]['serial_number']);
    }
}

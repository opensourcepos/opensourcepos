<?php

namespace Tests\Models;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\Item;
use App\Models\Item_quantity;
use App\Models\Transfer;

class TransferReversalTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = true;
    protected $namespace   = null;

    private int $item_id;
    private int $from_location;
    private int $to_location;

    protected function setUp(): void
    {
        parent::setUp();

        $this->from_location = 1;
        $this->to_location   = 2;

        // The test DB seeds only location 1; create a second one for the destination
        $db = \Config\Database::connect();
        if ($db->table('stock_locations')->where('location_id', 2)->countAllResults() === 0) {
            $db->table('stock_locations')->insert(['location_id' => 2, 'location_name' => 'test-loc-2']);
        }

        $item = model(Item::class);
        $item_data = [
            'name'           => 'Transfer Test Item',
            'item_number'    => 'TRF-TEST-' . uniqid(),
            'category'       => 'test',
            'supplier_id'    => null,
            'item_type'      => 0,
            'cost_price'     => 1.00,
            'unit_price'     => 2.00,
            'description'    => 'temp',
            'allow_alt_description' => 0,
            'is_serialized'  => 0,
            'deleted'        => 0
        ];
        $result = $item->save_value($item_data);
        $this->assertTrue($result);
        $this->assertGreaterThan(0, $item_data['item_id']);
        $this->item_id = (int) $item_data['item_id'];

        // Ensure stock locations 1 and 2 exist and give item 10 units at each
        $item_quantity = model(Item_quantity::class);
        $item_quantity->save_value(['item_id' => $this->item_id, 'location_id' => $this->from_location, 'quantity' => 10], $this->item_id, $this->from_location);
        $item_quantity->save_value(['item_id' => $this->item_id, 'location_id' => $this->to_location, 'quantity' => 10], $this->item_id, $this->to_location);
    }

    public function testSaveAndReverseTransferRestoresStock(): void
    {
        $item_quantity = model(Item_quantity::class);
        $transfer      = model(Transfer::class);

        $before_from = (float) $item_quantity->get_item_quantity($this->item_id, $this->from_location)->quantity;
        $before_to   = (float) $item_quantity->get_item_quantity($this->item_id, $this->to_location)->quantity;

        $cart = [
            1 => [
                'item_id'       => $this->item_id,
                'line'          => 1,
                'quantity'      => 3,
                'description'   => 'unit test transfer'
            ]
        ];

        $transfer_id = $transfer->save_value($cart, 1, $this->from_location, $this->to_location, 'unit test transfer');
        $this->assertNotEquals(-1, $transfer_id, 'transfer_id was -1');

        $this->assertEquals($before_from - 3, (float) $item_quantity->get_item_quantity($this->item_id, $this->from_location)->quantity);
        $this->assertEquals($before_to + 3, (float) $item_quantity->get_item_quantity($this->item_id, $this->to_location)->quantity);

        $this->assertTrue($transfer->delete_transfer($transfer_id));

        $this->assertEquals($before_from, (float) $item_quantity->get_item_quantity($this->item_id, $this->from_location)->quantity);
        $this->assertEquals($before_to, (float) $item_quantity->get_item_quantity($this->item_id, $this->to_location)->quantity);
    }
}

<?php

namespace Tests\Models;

use App\Models\Item;
use App\Models\Item_quantity;
use App\Models\Requisition;
use App\Models\Transfer;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;

/**
 * @internal
 */
final class RequisitionWorkflowTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = true;
    protected $namespace;
    private int $item_id;
    private int $from_location;
    private int $to_location;

    protected function setUp(): void
    {
        parent::setUp();

        $this->from_location = 1;
        $this->to_location   = 2;

        $db = Database::connect();
        if ($db->table('stock_locations')->where('location_id', 2)->countAllResults() === 0) {
            $db->table('stock_locations')->insert(['location_id' => 2, 'location_name' => 'test-loc-2']);
        }

        $item      = model(Item::class);
        $item_data = [
            'name'                  => 'Requisition Test Item',
            'item_number'           => 'REQ-TEST-' . uniqid(),
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
        $this->assertGreaterThan(0, $item_data['item_id']);
        $this->item_id = (int) $item_data['item_id'];

        $item_quantity = model(Item_quantity::class);
        $item_quantity->save_value(['item_id' => $this->item_id, 'location_id' => $this->from_location, 'quantity' => 10], $this->item_id, $this->from_location);
        $item_quantity->save_value(['item_id' => $this->item_id, 'location_id' => $this->to_location, 'quantity' => 10], $this->item_id, $this->to_location);
    }

    public function testRequisitionWorkflowMovesStockOnlyOnFinalApproval(): void
    {
        $item_quantity = model(Item_quantity::class);
        $requisition   = model(Requisition::class);

        $cart = [
            1 => [
                'item_id'     => $this->item_id,
                'line'        => 1,
                'quantity'    => 3,
                'description' => 'unit test requisition',
            ],
        ];

        // 1. Create request (PENDING), no stock may move yet
        $requisition_id = $requisition->save_value($cart, 1, $this->from_location, $this->to_location, 'unit test requisition');
        $this->assertNotSame(-1, $requisition_id);

        $info = $requisition->get_info($requisition_id)->getRowArray();
        $this->assertSame(Requisition::STATUS_PENDING, (int) $info['status']);

        $this->assertSame(10, (int) $item_quantity->get_item_quantity($this->item_id, $this->from_location)->quantity);
        $this->assertSame(10, (int) $item_quantity->get_item_quantity($this->item_id, $this->to_location)->quantity);

        // 2. Source approval only changes the status, stock still untouched
        $this->assertTrue($requisition->approve_source($requisition_id));

        $info = $requisition->get_info($requisition_id)->getRowArray();
        $this->assertSame(Requisition::STATUS_SOURCE_APPROVED, (int) $info['status']);

        $this->assertSame(10, (int) $item_quantity->get_item_quantity($this->item_id, $this->from_location)->quantity);
        $this->assertSame(10, (int) $item_quantity->get_item_quantity($this->item_id, $this->to_location)->quantity);

        // 3. Final approval moves the stock and marks the request APPROVED
        $this->assertTrue($requisition->approve($requisition_id, 1));

        $info = $requisition->get_info($requisition_id)->getRowArray();
        $this->assertSame(Requisition::STATUS_APPROVED, (int) $info['status']);
        $this->assertSame(1, (int) $info['approved_by']);

        $this->assertSame(7, (int) $item_quantity->get_item_quantity($this->item_id, $this->from_location)->quantity);
        $this->assertSame(13, (int) $item_quantity->get_item_quantity($this->item_id, $this->to_location)->quantity);

        // 4. Approval is recorded as a real transfer at the source
        $transfers   = Database::connect()->table('transfers');
        $transfer_id = null;

        foreach ($transfers->get()->getResultArray() as $transfer_row) {
            if ((int) $transfer_row['location_from'] === $this->from_location && (int) $transfer_row['location_to'] === $this->to_location) {
                $transfer_id = (int) $transfer_row['transfer_id'];
            }
        }
        $this->assertNotNull($transfer_id, 'No transfer was created for the approved requisition');
    }

    public function testRequisitionCanBeRejectedBeforeApproval(): void
    {
        $requisition = model(Requisition::class);

        $cart = [
            1 => [
                'item_id'     => $this->item_id,
                'line'        => 1,
                'quantity'    => 2,
                'description' => 'unit test requisition',
            ],
        ];

        $requisition_id = $requisition->save_value($cart, 1, $this->from_location, $this->to_location, 'reject me');
        $this->assertNotSame(-1, $requisition_id);

        $this->assertTrue($requisition->reject($requisition_id, 1));

        $info = $requisition->get_info($requisition_id)->getRowArray();
        $this->assertSame(Requisition::STATUS_REJECTED, (int) $info['status']);
    }

    public function testSameLocationRequisitionFails(): void
    {
        $requisition = model(Requisition::class);

        $cart = [
            1 => [
                'item_id'  => $this->item_id,
                'line'     => 1,
                'quantity' => 1,
            ],
        ];

        $requisition_id = $requisition->save_value($cart, 1, $this->from_location, $this->from_location, 'same location');
        $this->assertSame(-1, $requisition_id);
    }
}

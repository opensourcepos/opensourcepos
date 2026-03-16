<?php

declare(strict_types=1);

namespace Tests\Models\Reports;

use App\Models\Reports\Summary_taxes;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class Summary_taxes_test extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $migrateOnce = true;
    protected $refresh = true;
    protected $namespace = null;

    private array $seededSaleIds = [];
    private array $seededItemIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedTestData();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestData();
        parent::tearDown();
    }

    private function seedTestData(): void
    {
        $db = \Config\Database::connect();
        $prefix = $db->DBPrefix;

        $now = date('Y-m-d H:i:s');

        $itemData = [
            ['name' => 'Test Item Tax Test 1', 'category' => 'Test', 'supplier_id' => 1, 'item_number' => 'TEST001', 'description' => 'Test item for tax report', 'cost_price' => 10.00, 'unit_price' => 100.00, 'reorder_level' => 0, 'receiving_quantity' => 1, 'stock_type' => 0, 'item_type' => 0, 'tax_category_id' => 1, 'deleted' => 0],
            ['name' => 'Test Item Tax Test 2', 'category' => 'Test', 'supplier_id' => 1, 'item_number' => 'TEST002', 'description' => 'Test item for tax report', 'cost_price' => 20.00, 'unit_price' => 200.00, 'reorder_level' => 0, 'receiving_quantity' => 1, 'stock_type' => 0, 'item_type' => 0, 'tax_category_id' => 1, 'deleted' => 0],
        ];

        foreach ($itemData as $item) {
            $db->table($prefix . 'items')->insert($item);
            $this->seededItemIds[] = $db->insertID();
        }

        $saleData = [
            ['sale_time' => $now, 'customer_id' => 1, 'employee_id' => 1, 'comment' => 'Test sale 1', 'sale_status' => 1, 'invoice_number' => 'TEST-INV-001', 'sale_type' => 0],
            ['sale_time' => $now, 'customer_id' => 1, 'employee_id' => 1, 'comment' => 'Test sale 2', 'sale_status' => 1, 'invoice_number' => 'TEST-INV-002', 'sale_type' => 0],
            ['sale_time' => $now, 'customer_id' => 1, 'employee_id' => 1, 'comment' => 'Test sale 3', 'sale_status' => 1, 'invoice_number' => 'TEST-INV-003', 'sale_type' => 0],
        ];

        foreach ($saleData as $sale) {
            $db->table($prefix . 'sales')->insert($sale);
            $this->seededSaleIds[] = $db->insertID();
        }

        $salesItemsData = [
            ['sale_id' => $this->seededSaleIds[0], 'item_id' => $this->seededItemIds[0], 'line' => 1, 'description' => 'Item 1', 'quantity_purchased' => 1, 'item_unit_price' => 100.00, 'discount' => 0, 'discount_type' => 0, 'item_cost_price' => 50.00],
            ['sale_id' => $this->seededSaleIds[0], 'item_id' => $this->seededItemIds[1], 'line' => 2, 'description' => 'Item 2', 'quantity_purchased' => 2, 'item_unit_price' => 50.00, 'discount' => 0, 'discount_type' => 0, 'item_cost_price' => 25.00],
            ['sale_id' => $this->seededSaleIds[1], 'item_id' => $this->seededItemIds[0], 'line' => 1, 'description' => 'Item 1', 'quantity_purchased' => 1, 'item_unit_price' => 110.00, 'discount' => 10, 'discount_type' => 0, 'item_cost_price' => 55.00],
            ['sale_id' => $this->seededSaleIds[2], 'item_id' => $this->seededItemIds[1], 'line' => 1, 'description' => 'Item 2', 'quantity_purchased' => 1, 'item_unit_price' => 200.00, 'discount' => 0, 'discount_type' => 0, 'item_cost_price' => 100.00],
        ];

        foreach ($salesItemsData as $item) {
            $db->table($prefix . 'sales_items')->insert($item);
        }

        $salesItemsTaxesData = [
            ['sale_id' => $this->seededSaleIds[0], 'item_id' => $this->seededItemIds[0], 'line' => 1, 'name' => 'VAT', 'percent' => 10.00, 'item_tax_amount' => 10.00],
            ['sale_id' => $this->seededSaleIds[0], 'item_id' => $this->seededItemIds[1], 'line' => 2, 'name' => 'VAT', 'percent' => 10.00, 'item_tax_amount' => 10.00],
            ['sale_id' => $this->seededSaleIds[1], 'item_id' => $this->seededItemIds[0], 'line' => 1, 'name' => 'VAT', 'percent' => 10.00, 'item_tax_amount' => 11.00],
            ['sale_id' => $this->seededSaleIds[2], 'item_id' => $this->seededItemIds[1], 'line' => 1, 'name' => 'Sales Tax', 'percent' => 8.00, 'item_tax_amount' => 16.00],
        ];

        foreach ($salesItemsTaxesData as $tax) {
            $db->table($prefix . 'sales_items_taxes')->insert($tax);
        }
    }

    private function cleanupTestData(): void
    {
        $db = \Config\Database::connect();
        $prefix = $db->DBPrefix;

        if (!empty($this->seededSaleIds)) {
            $db->table($prefix . 'sales_items_taxes')
                ->whereIn('sale_id', $this->seededSaleIds)
                ->delete();
            $db->table($prefix . 'sales_items')
                ->whereIn('sale_id', $this->seededSaleIds)
                ->delete();
            $db->table($prefix . 'sales')
                ->whereIn('sale_id', $this->seededSaleIds)
                ->delete();
        }

        if (!empty($this->seededItemIds)) {
            $db->table($prefix . 'items')
                ->whereIn('item_id', $this->seededItemIds)
                ->delete();
        }

        $this->seededSaleIds = [];
        $this->seededItemIds = [];
    }

    public function testGetDataReturnsArray(): void
    {
        $model = model(Summary_taxes::class);
        $inputs = $this->getTestInputs();

        $result = $model->getData($inputs);

        $this->assertIsArray($result);
    }

    public function testGetDataHasExpectedColumns(): void
    {
        $model = model(Summary_taxes::class);
        $inputs = $this->getTestInputs();

        $result = $model->getData($inputs);

        $this->assertGreaterThan(0, count($result), 'Should have tax data from seeded sales');

        $row = $result[0];
        $this->assertArrayHasKey('name', $row);
        $this->assertArrayHasKey('percent', $row);
        $this->assertArrayHasKey('count', $row);
        $this->assertArrayHasKey('subtotal', $row);
        $this->assertArrayHasKey('tax', $row);
        $this->assertArrayHasKey('total', $row);
    }

    public function testGetSummaryDataReturnsArray(): void
    {
        $model = model(Summary_taxes::class);
        $inputs = $this->getTestInputs();

        $result = $model->getSummaryData($inputs);

        $this->assertIsArray($result);
    }

    public function testGetSummaryDataHasExpectedKeys(): void
    {
        $model = model(Summary_taxes::class);
        $inputs = $this->getTestInputs();

        $result = $model->getSummaryData($inputs);

        $this->assertArrayHasKey('subtotal', $result);
        $this->assertArrayHasKey('tax', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('count', $result);
    }

    public function testSummaryDataMatchesSumOfDataRows(): void
    {
        $model = model(Summary_taxes::class);
        $inputs = $this->getTestInputs();

        $data = $model->getData($inputs);
        $summary = $model->getSummaryData($inputs);

        $this->assertGreaterThan(0, count($data), 'Should have tax data from seeded sales');

        $subtotalSum = 0;
        $taxSum = 0;
        $totalSum = 0;
        $countSum = 0;

        foreach ($data as $row) {
            $subtotalSum += (float) $row['subtotal'];
            $taxSum += (float) $row['tax'];
            $totalSum += (float) $row['total'];
            $countSum += (int) $row['count'];
        }

        $decimals = 2;

        $this->assertEqualsWithDelta(
            round($subtotalSum, $decimals),
            $summary['subtotal'],
            0.01,
            'Summary subtotal should equal sum of row subtotals'
        );

        $this->assertEqualsWithDelta(
            round($taxSum, $decimals),
            $summary['tax'],
            0.01,
            'Summary tax should equal sum of row taxes'
        );

        $this->assertEqualsWithDelta(
            round($totalSum, $decimals),
            $summary['total'],
            0.01,
            'Summary total should equal sum of row totals'
        );

        $this->assertEquals(
            $countSum,
            $summary['count'],
            'Summary count should equal sum of row counts'
        );
    }

    public function testRowSubtotalPlusTaxEqualsTotal(): void
    {
        $model = model(Summary_taxes::class);
        $inputs = $this->getTestInputs();

        $data = $model->getData($inputs);

        $this->assertGreaterThan(0, count($data), 'Should have tax data from seeded sales');

        foreach ($data as $row) {
            $subtotal = (float) $row['subtotal'];
            $tax = (float) $row['tax'];
            $total = (float) $row['total'];
            $calculatedTotal = round($subtotal + $tax, 2);

            $this->assertEqualsWithDelta(
                $total,
                $calculatedTotal,
                0.01,
                "Row subtotal + tax should equal total for tax {$row['name']} ({$row['percent']}): subtotal={$subtotal}, tax={$tax}, total={$total}"
            );
        }
    }

    public function testGetDataColumnsReturnsExpectedStructure(): void
    {
        $model = model(Summary_taxes::class);

        $columns = $model->getDataColumns();

        $this->assertIsArray($columns);
        $this->assertCount(6, $columns);

        $expectedKeys = ['tax_name', 'tax_percent', 'report_count', 'subtotal', 'tax', 'total'];
        foreach ($columns as $index => $column) {
            $key = array_key_first($column);
            $this->assertEquals($expectedKeys[$index], $key);
        }
    }

    public function testTaxDataIsGroupedByTaxNameAndPercent(): void
    {
        $model = model(Summary_taxes::class);
        $inputs = $this->getTestInputs();

        $data = $model->getData($inputs);

        $this->assertGreaterThan(0, count($data), 'Should have tax data from seeded sales');

        $taxGroups = [];
        foreach ($data as $row) {
            $key = $row['name'] . '|' . $row['percent'];
            $this->assertArrayNotHasKey($key, $taxGroups, "Each tax name+percent combination should appear only once in results");
            $taxGroups[$key] = true;
        }
    }

    private function getTestInputs(): array
    {
        return [
            'start_date' => date('Y-m-d', strtotime('-1 day')),
            'end_date' => date('Y-m-d', strtotime('+1 day')),
            'sale_type' => 'complete',
            'location_id' => 'all'
        ];
    }
}
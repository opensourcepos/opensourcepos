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

    protected function setUp(): void
    {
        parent::setUp();
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

        if (count($result) > 0) {
            $row = $result[0];
            $this->assertArrayHasKey('name', $row);
            $this->assertArrayHasKey('percent', $row);
            $this->assertArrayHasKey('count', $row);
            $this->assertArrayHasKey('subtotal', $row);
            $this->assertArrayHasKey('tax', $row);
            $this->assertArrayHasKey('total', $row);
        } else {
            $this->markTestSkipped('No sales tax data in test database');
        }
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

        if (count($data) === 0) {
            $this->markTestSkipped('No sales tax data in test database');
        }

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

        if (count($data) === 0) {
            $this->markTestSkipped('No sales tax data in test database');
        }

        foreach ($data as $row) {
            $subtotal = (float) $row['subtotal'];
            $tax = (float) $row['tax'];
            $total = (float) $row['total'];
            $calculatedTotal = round($subtotal + $tax, 2);

            $this->assertEqualsWithDelta(
                $total,
                $calculatedTotal,
                0.01,
                "Row subtotal + tax should equal total: {$subtotal} + {$tax} should equal {$total}"
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

    private function getTestInputs(): array
    {
        return [
            'start_date' => date('Y-m-d', strtotime('-1 year')),
            'end_date' => date('Y-m-d', strtotime('+1 day')),
            'sale_type' => 'complete',
            'location_id' => 'all'
        ];
    }
}
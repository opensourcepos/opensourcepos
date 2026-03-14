<?php

declare(strict_types=1);

namespace Tests\Models\Reports;

use CodeIgniter\Test\CIUnitTestCase;

class Summary_taxes_test extends CIUnitTestCase
{
    public function testRowTotalsAddUp(): void
    {
        $rows = [
            ['subtotal' => 100.00, 'tax' => 10.00, 'total' => 110.00],
            ['subtotal' => 200.00, 'tax' => 20.00, 'total' => 220.00],
            ['subtotal' => 50.00, 'tax' => 5.00, 'total' => 55.00],
        ];

        foreach ($rows as $row) {
            $calculatedTotal = round((float) $row['subtotal'] + (float) $row['tax'], 2);
            $this->assertEquals(
                (float) $row['total'],
                $calculatedTotal,
                "Row subtotal + tax should equal total: {$row['subtotal']} + {$row['tax']} = {$row['total']}"
            );
        }
    }

    public function testSummaryTotalsMatchRowSums(): void
    {
        $rows = [
            ['subtotal' => 100.00, 'tax' => 10.00, 'total' => 110.00, 'count' => 5],
            ['subtotal' => 200.00, 'tax' => 20.00, 'total' => 220.00, 'count' => 10],
            ['subtotal' => 50.00, 'tax' => 5.00, 'total' => 55.00, 'count' => 3],
        ];

        $summary = $this->calculateSummary($rows, 2);

        $expectedSubtotal = 0;
        $expectedTax = 0;
        $expectedTotal = 0;
        $expectedCount = 0;

        foreach ($rows as $row) {
            $expectedSubtotal += (float) $row['subtotal'];
            $expectedTax += (float) $row['tax'];
            $expectedTotal += (float) $row['total'];
            $expectedCount += (int) $row['count'];
        }

        $this->assertEquals(
            round($expectedSubtotal, 2),
            $summary['subtotal'],
            'Summary subtotal should equal sum of row subtotals'
        );
        $this->assertEquals(
            round($expectedTax, 2),
            $summary['tax'],
            'Summary tax should equal sum of row taxes'
        );
        $this->assertEquals(
            round($expectedTotal, 2),
            $summary['total'],
            'Summary total should equal sum of row totals'
        );
        $this->assertEquals(
            $expectedCount,
            $summary['count'],
            'Summary count should equal sum of row counts'
        );
    }

    public function testSubtotalPlusTaxEqualsTotalInSummary(): void
    {
        $rows = [
            ['subtotal' => 100.00, 'tax' => 10.00, 'total' => 110.00, 'count' => 5],
            ['subtotal' => 200.00, 'tax' => 20.00, 'total' => 220.00, 'count' => 10],
        ];

        $summary = $this->calculateSummary($rows, 2);

        $calculatedTotal = round($summary['subtotal'] + $summary['tax'], 2);
        $this->assertEquals(
            $summary['total'],
            $calculatedTotal,
            'Summary subtotal + tax should equal summary total'
        );
    }

    public function testRoundingConsistency(): void
    {
        $rows = [
            ['subtotal' => 99.99, 'tax' => 9.999, 'total' => 109.989],
            ['subtotal' => 33.33, 'tax' => 3.333, 'total' => 36.663],
        ];

        $summary = $this->calculateSummary($rows, 2);

        $sumOfRoundedSubtotals = 0;
        $sumOfRoundedTaxes = 0;

        foreach ($rows as $row) {
            $sumOfRoundedSubtotals += round((float) $row['subtotal'], 2);
            $sumOfRoundedTaxes += round((float) $row['tax'], 2);
        }

        $expectedTotal = round($sumOfRoundedSubtotals + $sumOfRoundedTaxes, 2);

        $this->assertEquals(
            $expectedTotal,
            $summary['total'],
            'Total should be sum of rounded subtotals + sum of rounded taxes'
        );
    }

    public function testTaxIncludedModeCalculation(): void
    {
        $saleAmount = 110.00;
        $taxAmount = 10.00;

        $subtotal = round($saleAmount - $taxAmount, 2);
        $total = round($saleAmount, 2);

        $this->assertEquals(100.00, $subtotal, 'Tax-included: subtotal should be sale_amount - tax');
        $this->assertEquals(110.00, $total, 'Tax-included: total should be sale_amount');
        $this->assertEquals(
            $total,
            round($subtotal + $taxAmount, 2),
            'Tax-included: total should equal subtotal + tax'
        );
    }

    public function testTaxNotIncludedModeCalculation(): void
    {
        $saleAmount = 100.00;
        $taxAmount = 10.00;

        $subtotal = round($saleAmount, 2);
        $total = round($saleAmount + $taxAmount, 2);

        $this->assertEquals(100.00, $subtotal, 'Tax-not-included: subtotal should be sale_amount');
        $this->assertEquals(110.00, $total, 'Tax-not-included: total should be sale_amount + tax');
        $this->assertEquals(
            $total,
            round($subtotal + $taxAmount, 2),
            'Tax-not-included: total should equal subtotal + tax'
        );
    }

    public function testNegativeValuesForReturns(): void
    {
        $rows = [
            ['subtotal' => -100.00, 'tax' => -10.00, 'total' => -110.00, 'count' => 1],
            ['subtotal' => 200.00, 'tax' => 20.00, 'total' => 220.00, 'count' => 2],
        ];

        foreach ($rows as $row) {
            $calculatedTotal = round((float) $row['subtotal'] + (float) $row['tax'], 2);
            $this->assertEquals(
                (float) $row['total'],
                $calculatedTotal,
                "Negative row: subtotal + tax should equal total"
            );
        }

        $summary = $this->calculateSummary($rows, 2);

        $this->assertEquals(100.00, $summary['subtotal'], 'Summary should handle negative values');
        $this->assertEquals(10.00, $summary['tax'], 'Summary tax should handle negative values');
        $this->assertEquals(110.00, $summary['total'], 'Summary total should handle negative values');
    }

    public function testZeroTaxRows(): void
    {
        $rows = [
            ['subtotal' => 100.00, 'tax' => 0.00, 'total' => 100.00, 'count' => 1],
            ['subtotal' => 200.00, 'tax' => 0.00, 'total' => 200.00, 'count' => 2],
        ];

        foreach ($rows as $row) {
            $this->assertEquals(
                (float) $row['subtotal'],
                (float) $row['total'],
                'Zero tax: subtotal should equal total'
            );
        }

        $summary = $this->calculateSummary($rows, 2);

        $this->assertEquals(
            $summary['subtotal'],
            $summary['total'],
            'Zero tax summary: subtotal should equal total'
        );
    }

    private function calculateSummary(array $rows, int $decimals = 2): array
    {
        $subtotal = 0;
        $tax = 0;
        $total = 0;
        $count = 0;

        foreach ($rows as $row) {
            $subtotal += (float) $row['subtotal'];
            $tax += (float) $row['tax'];
            $total += (float) $row['total'];
            $count += (int) $row['count'];
        }

        return [
            'subtotal' => round($subtotal, $decimals),
            'tax' => round($tax, $decimals),
            'total' => round($total, $decimals),
            'count' => $count,
        ];
    }
}
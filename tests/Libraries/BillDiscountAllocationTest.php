<?php

namespace Tests\Libraries;

use App\Libraries\BillDiscountAllocation;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;

/**
 * @internal
 */
final class BillDiscountAllocationTest extends CIUnitTestCase
{
    public function testAllocateDistributesRoundingRemainder(): void
    {
        $this->assertSame(['a' => 34, 'b' => 33, 'c' => 33], BillDiscountAllocation::allocate(['a' => 100, 'b' => 100, 'c' => 100], 100));
    }

    public function testAllocatePreservesZeroAndFullDiscount(): void
    {
        $this->assertSame([0, 25, 75], BillDiscountAllocation::allocate([0, 25, 75], 100));
        $this->assertSame([0, 0], BillDiscountAllocation::allocate([0, 0], 0));
    }

    public function testAllocatePercentageRoundsAndAllocates(): void
    {
        $this->assertSame([13, 12], BillDiscountAllocation::allocatePercentage([100, 100], 125000));
        $this->assertSame([1], BillDiscountAllocation::allocatePercentage([1], 500000));
        $this->assertSame([100], BillDiscountAllocation::allocatePercentage([100], 1000000));
    }

    public function testAllocatePercentageRejectsRateAboveHundredPercent(): void
    {
        $this->expectException(InvalidArgumentException::class);
        BillDiscountAllocation::allocatePercentage([100], 1000001);
    }

    public function testAllocatePreservesAmountsAcrossMixedLines(): void
    {
        $amounts = [0, 1, 79, 1000];

        foreach ([0, 1, 17, 537, 1080] as $discount) {
            $allocation = BillDiscountAllocation::allocate($amounts, $discount);
            $this->assertSame($discount, array_sum($allocation));

            foreach ($allocation as $line => $amount) {
                $this->assertGreaterThanOrEqual(0, $amount);
                $this->assertLessThanOrEqual($amounts[$line], $amount);
            }
        }
    }

    public function testAllocateRejectsNegativeLine(): void
    {
        $this->expectException(InvalidArgumentException::class);
        BillDiscountAllocation::allocate([-1, 100], 1);
    }

    public function testAllocateRejectsDiscountAboveTotal(): void
    {
        $this->expectException(InvalidArgumentException::class);
        BillDiscountAllocation::allocate([100], 101);
    }
}

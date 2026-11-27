<?php

namespace Tests\Libraries;

use App\Libraries\Sale_lib;
use App\Libraries\Tax_lib;
use CodeIgniter\Test\CIUnitTestCase;
use ReflectionClass;

/**
 * @internal
 */
final class Tax_libTest extends CIUnitTestCase
{
    public function testGetIncludedTaxSubtractsBillDiscountFromTaxableAmount(): void
    {
        $sale = $this->getMockBuilder(Sale_lib::class)->disableOriginalConstructor()->getMock();
        $sale->method('get_item_total')->willReturn('120.00');
        $reflection = new ReflectionClass(Tax_lib::class);
        $tax        = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('sale_lib')->setValue($tax, $sale);

        $oldScale = bcscale(4);

        try {
            $this->assertEqualsWithDelta(20.0, (float) $tax->get_included_tax('1', '120', '0', 0, '20', 2, 1), 0.0001);
            $this->assertEqualsWithDelta(18.0, (float) $tax->get_included_tax('1', '120', '0', 0, '20', 2, 1, '12'), 0.0001);
        } finally {
            bcscale($oldScale);
        }
    }
}

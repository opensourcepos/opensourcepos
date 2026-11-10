<?php

namespace Tests\Libraries;

use App\Libraries\Tax_lib;
use App\Models\Enums\Rounding_mode;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\OsposConfigFixtureTrait;

class Tax_libTest extends CIUnitTestCase
{
    use OsposConfigFixtureTrait;

    private Tax_lib $taxLib;

    protected function setUp(): void
    {
        parent::setUp();
        require_once APPPATH . 'Helpers/locale_helper.php';

        $this->injectOsposSettings([
            'tax_included'        => false,
            'tax_decimals'        => 2,
            'currency_decimals'   => 2,
            'number_locale'       => 'en_US',
            'thousands_separator' => true,
            'default_tax_code'    => 7,
        ]);

        $this->taxLib = new Tax_lib();
    }

    public function testRoundTaxes_RoundsUpAndDownCorrectlyForTwoDecimals(): void
    {
        $taxes = [
            'up' => [
                'print_sequence'  => 1,
                'sale_tax_amount' => 1.231,
                'cash_tax_amount' => 1.231,
                'rounding_code'   => Rounding_mode::ROUND_UP,
            ],
            'down' => [
                'print_sequence'  => 2,
                'sale_tax_amount' => 1.239,
                'cash_tax_amount' => 1.239,
                'rounding_code'   => Rounding_mode::ROUND_DOWN,
            ],
            'half_odd' => [
                'print_sequence'  => 3,
                'sale_tax_amount' => 1.225,
                'cash_tax_amount' => 1.225,
                'rounding_code'   => Rounding_mode::HALF_ODD,
            ],
            'half_five' => [
                'print_sequence'  => 4,
                'sale_tax_amount' => 1.23,
                'cash_tax_amount' => 1.23,
                'rounding_code'   => Rounding_mode::HALF_FIVE,
            ],
        ];

        $this->taxLib->round_taxes($taxes);

        $this->assertEqualsWithDelta(1.24, (float) $taxes['up']['sale_tax_amount'], 0.0001);
        $this->assertEqualsWithDelta(1.23, (float) $taxes['down']['sale_tax_amount'], 0.0001);
        $this->assertEqualsWithDelta(1.23, (float) $taxes['half_odd']['sale_tax_amount'], 0.0001);
        $this->assertEqualsWithDelta(1.25, (float) $taxes['half_five']['sale_tax_amount'], 0.0001);
    }

    public function testGetApplicableTaxCode_AcceptsNullCustomerFieldsForSaleMode(): void
    {
        $result = $this->taxLib->get_applicable_tax_code('sale', null, null, null);

        $this->assertSame(7, $result);
    }

    public function testGetApplicableTaxCode_ReturnsCustomerTaxCodeForInvoiceMode(): void
    {
        $result = $this->taxLib->get_applicable_tax_code('sale_invoice', null, null, 12);

        $this->assertSame(12, $result);
    }
}

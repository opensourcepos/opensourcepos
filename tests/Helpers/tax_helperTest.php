<?php

use App\Models\Enums\Rounding_mode;
use CodeIgniter\Test\CIUnitTestCase;

class tax_helperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/../../app/Helpers/tax_helper.php';
    }

    public function testTaxRateExportHeaders_ReturnsExactlySixColumns(): void
    {
        $headers = taxRateExportHeaders();

        $this->assertCount(6, $headers);

        $keys = array_map('array_key_first', $headers);
        $this->assertSame(
            ['tax_code', 'tax_code_name', 'jurisdiction_name', 'tax_category', 'tax_rate', 'rounding_code_name'],
            $keys
        );
    }

    public function testGetTaxRateExportRow_ContainsAllTenKeysIncludingHiddenForeignKeys(): void
    {
        $taxRatesRow = $this->buildTaxRatesRow();

        $row = getTaxRateExportRow($taxRatesRow);

        $this->assertSame(
            [
                'tax_rate_id',
                'tax_code',
                'tax_code_name',
                'tax_category',
                'tax_rate',
                'jurisdiction_name',
                'rounding_code_name',
                'rate_tax_code_id',
                'rate_tax_category_id',
                'rate_jurisdiction_id',
            ],
            array_keys($row)
        );
    }

    public function testGetTaxRateExportRow_ReturnsRawUnformattedValues(): void
    {
        $taxRatesRow = $this->buildTaxRatesRow();

        $row = getTaxRateExportRow($taxRatesRow);

        $this->assertSame(1, $row['tax_rate_id']);
        $this->assertSame('TAX1', $row['tax_code']);
        $this->assertSame('Standard Tax', $row['tax_code_name']);
        $this->assertSame('Sales Tax', $row['tax_category']);
        $this->assertSame(7.25, $row['tax_rate']);
        $this->assertSame('Illinois', $row['jurisdiction_name']);
        $this->assertSame(2, $row['rate_tax_code_id']);
        $this->assertSame(3, $row['rate_tax_category_id']);
        $this->assertSame(4, $row['rate_jurisdiction_id']);
    }

    public function testGetTaxRateExportRow_ResolvesRoundingCodeNameViaRoundingModeModel(): void
    {
        $taxRatesRow = $this->buildTaxRatesRow(['tax_rounding_code' => Rounding_mode::HALF_UP]);

        $row = getTaxRateExportRow($taxRatesRow);

        $this->assertSame(
            Rounding_mode::get_rounding_code_name(Rounding_mode::HALF_UP),
            $row['rounding_code_name']
        );
    }

    private function buildTaxRatesRow(array $overrides = []): object
    {
        return (object) array_merge([
            'tax_rate_id'          => 1,
            'tax_code'             => 'TAX1',
            'tax_code_name'        => 'Standard Tax',
            'tax_category'         => 'Sales Tax',
            'tax_rate'             => 7.25,
            'jurisdiction_name'    => 'Illinois',
            'tax_rounding_code'    => Rounding_mode::HALF_UP,
            'rate_tax_code_id'     => 2,
            'rate_tax_category_id' => 3,
            'rate_jurisdiction_id' => 4,
        ], $overrides);
    }
}

<?php

namespace Tests\Jobs\Support;

use App\Jobs\Support\ItemCsvRowProcessor;
use CodeIgniter\Test\CIUnitTestCase;

class ItemCsvRowProcessorTest extends CIUnitTestCase
{
    public function testSaveTaxData_RejectsNegativePercent(): void
    {
        $processor = (new \ReflectionClass(ItemCsvRowProcessor::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod($processor, 'saveTaxData');
        $method->setAccessible(true);

        $row = [
            'Tax 1 Name'    => 'VAT',
            'Tax 1 Percent' => '-5',
            'Tax 2 Name'    => '',
            'Tax 2 Percent' => '',
        ];

        $this->assertFalse($method->invoke($processor, $row, ['item_id' => 1]));
    }

    public function testSaveTaxData_RejectsNegativePercentOnTax2(): void
    {
        $processor = (new \ReflectionClass(ItemCsvRowProcessor::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod($processor, 'saveTaxData');
        $method->setAccessible(true);

        $row = [
            'Tax 1 Name'    => '',
            'Tax 1 Percent' => '',
            'Tax 2 Name'    => 'GST',
            'Tax 2 Percent' => '-10',
        ];

        $this->assertFalse($method->invoke($processor, $row, ['item_id' => 1]));
    }
}

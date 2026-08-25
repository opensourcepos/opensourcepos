<?php

namespace Tests\Libraries;

use App\Libraries\Barcode_lib;
use CodeIgniter\Test\CIUnitTestCase;

class Barcode_libTest extends CIUnitTestCase
{
    private Barcode_lib $barcodeLib;

    protected function setUp(): void
    {
        parent::setUp();

        $this->barcodeLib = new Barcode_lib();
    }

    private function baseBarcodeConfig(string $layout): array
    {
        return [
            'company'                    => 'Test Co',
            'barcode_content'            => 'id',
            'barcode_type'               => 'C128',
            'barcode_font'               => 'inconsolata.ttf',
            'barcode_font_size'          => 10,
            'barcode_height'             => 40,
            'barcode_width'              => 2,
            'barcode_first_row'          => $layout,
            'barcode_second_row'         => 'none',
            'barcode_third_row'          => 'none',
            'barcode_num_in_row'         => 1,
            'barcode_page_width'         => 8,
            'barcode_page_cellspacing'   => 1,
            'barcode_generate_if_empty'  => 0,
            'barcode_formats'            => [],
        ];
    }

    public function testNamePayloadIsEscaped(): void
    {
        $item = [
            'name'    => '<svg onload=alert(document.domain)>',
            'item_id' => '1',
        ];

        $result = $this->barcodeLib->display_barcode($item, $this->baseBarcodeConfig('name'));

        $this->assertStringNotContainsString('<svg onload', $result);
        $this->assertStringContainsString('&lt;svg', $result);
    }

    public function testItemCodeIdPayloadIsEscaped(): void
    {
        $item = [
            'name'    => 'Item Name',
            'item_id' => 'KIT 1<svg onload=alert(document.domain)>',
        ];

        $config = $this->baseBarcodeConfig('item_code');
        $config['barcode_content'] = 'id';

        $result = $this->barcodeLib->display_barcode($item, $config);

        $this->assertStringNotContainsString('<svg onload', $result);
        $this->assertStringContainsString('&lt;svg', $result);
    }

    public function testItemCodeNumberPayloadIsEscaped(): void
    {
        $item = [
            'name'        => 'Item Name',
            'item_id'     => '1',
            'item_number' => 'KIT 1<svg onload=alert(document.domain)>',
        ];

        $config = $this->baseBarcodeConfig('item_code');
        $config['barcode_content'] = 'item_number';

        $result = $this->barcodeLib->display_barcode($item, $config);

        $this->assertStringNotContainsString('<svg onload', $result);
        $this->assertStringContainsString('&lt;svg', $result);
    }

    public function testCategoryPayloadIsEscaped(): void
    {
        $item = [
            'name'     => 'Item Name',
            'item_id'  => '1',
            'category' => '<svg onload=alert(document.domain)>',
        ];

        $result = $this->barcodeLib->display_barcode($item, $this->baseBarcodeConfig('category'));

        $this->assertStringNotContainsString('<svg onload', $result);
        $this->assertStringContainsString('&lt;svg', $result);
    }

    public function testCleanNameIsUnaffected(): void
    {
        $item = [
            'name'    => 'Widget A',
            'item_id' => '1',
        ];

        $result = $this->barcodeLib->display_barcode($item, $this->baseBarcodeConfig('name'));

        $this->assertStringContainsString('Widget A', $result);
    }
}

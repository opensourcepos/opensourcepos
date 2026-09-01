<?php

namespace Tests\Controllers;

use CodeIgniter\Config\Factories;
use CodeIgniter\Database\Config;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\Item;
use App\Models\Item_kit;
use Config\OSPOS;
use Exception;

class ItemKitsControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $migrateOnce = true;
    protected $seedOnce = true;
    protected $refresh = false;
    protected $namespace = null;

    private static bool $doneBootstrap = false;

    protected Item $item;
    protected Item_kit $itemKit;

    protected function setUp(): void
    {
        if (self::$doneBootstrap === false) {
            Config::seeder($this->DBGroup)->call('App\Database\Seeds\TestDatabaseBootstrapSeeder');
            Config::connect($this->DBGroup)->close();

            self::$doneBootstrap = true;
        }

        parent::setUp();

        $ospos = new OSPOS();
        $ospos->settings = [
            'company'                   => 'Test Co',
            'barcode_content'           => 'id',
            'barcode_type'              => 'C128',
            'barcode_font'              => 'inconsolata.ttf',
            'barcode_font_size'         => 10,
            'barcode_height'            => 40,
            'barcode_width'             => 2,
            'barcode_first_row'         => 'item_code',
            'barcode_second_row'        => 'none',
            'barcode_third_row'         => 'none',
            'barcode_num_in_row'        => 1,
            'barcode_page_width'        => 8,
            'barcode_page_cellspacing'  => 1,
            'barcode_generate_if_empty' => 0,
            'barcode_formats'           => 'null',
        ];
        Factories::injectMock('config', OSPOS::class, $ospos);

        $this->item = model(Item::class);
        $this->itemKit = model(Item_kit::class);
    }

    protected function tearDown(): void
    {
        Factories::reset();
        parent::tearDown();
    }

    protected function loginAsAdmin(): void
    {
        $this->withSession([
            'person_id'  => 1,
            'menu_group' => 'office'
        ]);
    }

    private function createItemKit(): int
    {
        $itemData = [
            'item_id' => null,
            'name' => 'Kit Base Item',
            'category' => 'Test',
            'cost_price' => 10.00,
            'unit_price' => 20.00,
            'deleted' => 0
        ];
        $this->assertTrue($this->item->save_value($itemData));

        $itemKitData = [
            'name' => 'Test Kit',
            'description' => 'Test Kit Description',
            'item_id' => $itemData['item_id'],
            'kit_discount' => 0,
            'kit_discount_type' => 0,
            'price_option' => 0,
            'print_option' => 0
        ];
        $this->assertTrue($this->itemKit->save_value($itemKitData));

        return (int) $itemKitData['item_kit_id'];
    }

    /**
     * @throws Exception
     */
    public function testGenerateBarcodesDoesNotDecodeTripleEncodedPayload(): void
    {
        $itemKitId = $this->createItemKit();
        $this->loginAsAdmin();

        // <svg onload=alert(document.domain)> URL-encoded three times (GHSA-3vpv-jqr3-7256 PoC).
        // The framework's router decodes this twice before routing; the controller used to apply
        // a third urldecode(), turning the remaining %3C.../%3E into a live <svg onload=...> tag.
        // With that urldecode() removed, the value must stay percent-encoded text and never
        // become a raw '<' in the response.
        $payload = $itemKitId . '%25253Csvg%252520onload%25253Dalert%252528document.domain%252529%25253E';

        $response = $this->get('/item_kits/generateBarcodes/' . $payload);
        $response->assertStatus(200);

        $body = $response->getBody();
        $this->assertStringNotContainsString('<svg onload', $body);
        $this->assertStringContainsString('%3Csvg', $body);
    }

    public function testGenerateBarcodesWorksForPlainItemKitId(): void
    {
        $itemKitId = $this->createItemKit();
        $this->loginAsAdmin();

        $response = $this->get('/item_kits/generateBarcodes/' . $itemKitId);

        $response->assertStatus(200);
        $this->assertStringContainsString('KIT ' . $itemKitId, $response->getBody());
    }
}

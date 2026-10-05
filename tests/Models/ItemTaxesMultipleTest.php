<?php

namespace Tests\Models;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\Item_taxes;
use Config\Database;
use Tests\Support\ItemSearchFixtureTrait;

class ItemTaxesMultipleTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use ItemSearchFixtureTrait;

    protected $migrate = true;
    protected $migrateOnce = true;
    protected $seed = '';
    protected $seedOnce = true;
    protected $refresh = true;
    protected $namespace = null;

    protected $item_taxes;

    public static function setUpBeforeClass(): void
    {
        $seeder = Database::seeder('tests');
        $seeder->call('TestDatabaseBootstrapSeeder');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->item_taxes = model(Item_taxes::class);
    }

    public function testGetInfoMultipleReturnsTaxesGroupedByItemId(): void
    {
        $itemOneId = $this->createSearchableItem();
        $itemTwoId = $this->createSearchableItem();

        $itemOneTaxes = [['name' => 'VAT', 'percent' => 20]];
        $itemTwoTaxes = [['name' => 'GST', 'percent' => 10]];

        $this->item_taxes->save_value($itemOneTaxes, $itemOneId);
        $this->item_taxes->save_value($itemTwoTaxes, $itemTwoId);

        $result = $this->item_taxes->getInfoMultiple([$itemOneId, $itemTwoId]);

        $this->assertArrayHasKey($itemOneId, $result);
        $this->assertArrayHasKey($itemTwoId, $result);
        $this->assertCount(1, $result[$itemOneId]);
        $this->assertCount(1, $result[$itemTwoId]);
        $this->assertEquals('VAT', $result[$itemOneId][0]['name']);
        $this->assertEquals('GST', $result[$itemTwoId][0]['name']);
    }

    public function testGetInfoMultipleItemWithNoTaxesOmittedFromResult(): void
    {
        $itemWithTaxesId = $this->createSearchableItem();
        $itemWithoutTaxesId = $this->createSearchableItem();

        $taxes = [['name' => 'VAT', 'percent' => 20]];
        $this->item_taxes->save_value($taxes, $itemWithTaxesId);

        $result = $this->item_taxes->getInfoMultiple([$itemWithTaxesId, $itemWithoutTaxesId]);

        $this->assertArrayHasKey($itemWithTaxesId, $result);
        $this->assertArrayNotHasKey($itemWithoutTaxesId, $result);
    }

    public function testGetInfoMultipleEmptyItemIdsReturnsEmptyArray(): void
    {
        $result = $this->item_taxes->getInfoMultiple([]);

        $this->assertSame([], $result);
    }
}

<?php

namespace Racecore\GATracking\Tracking\Ecommerce;

use Racecore\GATracking\AbstractGATrackingTest;

/**
 * Class ItemTest
 *
 * @author      Enea Berti
 * @package     Racecore\GATracking\Tracking\Ecommerce
 */
class ItemTest extends AbstractGATrackingTest
{
    public function testPaketEqualsSpecification()
    {
        $item = new Item();

        $item->setTransactionID('1234');
        $item->setName('Product name');
        $item->setPrice(123.45);
        $item->setQuantity(1);
        $item->setSku('product_sku');
        $item->setCategory('Category');
        $item->setCurrency('EUR');
        $item->setDocumentHost('www.domain.tld');

        $packet = $item->getPackage();

        $this->assertEquals(
            array(
                't' => 'item',
                'ti' => '1234',
                'in' => 'Product name',
                'ip' => 123.45,
                'iq' => 1,
                'ic' => 'product_sku',
                'iv' => 'Category',
                'cu' => 'EUR',
                'dh' => 'www.domain.tld',
            ),
            $packet
        );
    }
}

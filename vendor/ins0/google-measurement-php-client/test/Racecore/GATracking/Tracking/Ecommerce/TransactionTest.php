<?php

namespace Racecore\GATracking\Tracking\Ecommerce;

use Racecore\GATracking\AbstractGATrackingTest;

/**
 * Class TransactionTest
 *
 * @author      Enea Berti
 * @package     Racecore\GATracking\Tracking\Ecommerce
 */
class TransactionTest extends AbstractGATrackingTest
{
    public function testPaketEqualsSpecification()
    {
        $transaction = new Transaction();

        $transaction->setID('1234');
        $transaction->setAffiliation('Affiliation name');
        $transaction->setRevenue(123.45);
        $transaction->setShipping(12.34);
        $transaction->setTax(12.34);
        $transaction->setCurrency('EUR');
        $transaction->setTransactionHost('www.domain.tld');

        $packet = $transaction->getPackage();

        $this->assertEquals(
            array(
                't' => 'transaction',
                'ti' => '1234',
                'ta' => 'Affiliation name',
                'tr' => 123.45,
                'ts' => 12.34,
                'tt' => 12.34,
                'cu' => 'EUR',
                'dh' => 'www.domain.tld',
            ),
            $packet
        );
    }
}

<?php

namespace Racecore\GATracking\Tracking\User;

use Racecore\GATracking\AbstractGATrackingTest;

/**
 * Class User Timing Test
 *
 * @author      Marco Rieger
 * @package     Racecore\GATracking\Tracking\User
 */
class TimingTest extends AbstractGATrackingTest
{
    public function testPaketEqualsSpecification()
    {
        $timing = new Timing();

        $timing->setTimingCategory('Timing Category');
        $timing->setTimingVariable('Timing Variable');
        $timing->setTimingTime(1);
        $timing->setTimingLabel('Timing Label');
        $timing->setBrowserDnsLoadTime(2);
        $timing->setBrowserPageDownloadTime(3);
        $timing->setBrowserRedirectTime(4);
        $timing->setBrowserTcpConnectTime(5);
        $timing->setBrowserServerResponseTime(6);

        $packet = $timing->getPackage();

        $this->assertEquals(
            array(
                't' => 'timing',
                'utc' => 'Timing Category',
                'utv' => 'Timing Variable',
                'utt' => 1,
                'utl' => 'Timing Label',
                'dns' => 2,
                'pdt' => 3,
                'rrt' => 4,
                'tcp' => 5,
                'srt' => 6
            ),
            $packet
        );
    }
}

<?php

namespace Racecore\GATracking\Tracking;

use Racecore\GATracking\AbstractGATrackingTest;

/**
 * Class PageviewTest
 *
 * @author      Marco Rieger
 * @package       Racecore\GATracking\Tracking
 */
class SocialTest extends AbstractGATrackingTest
{
    public function testPaketEqualsSpecification()
    {
        /** @var Social $social */
        $social = new Social();
        $social->setSocialAction('Test Action');
        $social->setSocialNetwork('Test Network');
        $social->setSocialTarget('/test-target');

        $packet = $social->getPackage();
        $this->assertEquals(
            array(
                't'     =>  'social',
                'sa'    =>  'Test Action',
                'sn'    =>  'Test Network',
                'st'    =>  '/test-target'
            ),
            $packet
        );
    }
}

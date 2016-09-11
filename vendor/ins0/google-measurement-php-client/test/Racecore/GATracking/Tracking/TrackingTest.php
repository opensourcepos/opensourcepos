<?php

namespace Racecore\GATracking\Tracking;

use Racecore\GATracking\AbstractGATrackingTest;

/**
 * Class TrackingTest
 *
 * @package       Racecore\GATracking\Tracking
 */
class TrackingTest extends AbstractGATrackingTest
{
    public function testCanSetTrackingProcessingTime()
    {
        $queueTime = 12345678;
        
        $event = new Event();
        $event->setEventCategory('foo');
        $event->setEventAction('bar');
        $event->setQueueTime($queueTime);

        $package = $event->getPackage();
        $this->assertEquals($queueTime, $package['qt']);
    }
}

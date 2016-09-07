<?php

namespace Racecore\GATracking\Tracking;

use Racecore\GATracking\AbstractGATrackingTest;

/**
 * Class Event Test
 *
 * @author      Marco Rieger
 * @package     Racecore\GATracking\Tracking
 */
class EventTest extends AbstractGATrackingTest
{
    public function testPaketEqualsSpecification()
    {
        /** @var Event $event */
        $event = new Event();
        $event->setEventCategory('foo');
        $event->setEventAction('bar');
        $event->setEventLabel('baz');
        $event->setEventValue('val');

        $packet = $event->getPackage();
        $this->assertEquals(
            array(
                't'     =>  'event',
                'ec'    =>  'foo',
                'ea'    =>  'bar',
                'el'    =>  'baz',
                'ev'    =>  'val'
            ),
            $packet
        );
    }
}

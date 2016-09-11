<?php

namespace Racecore\GATracking\Tracking\App;

use Racecore\GATracking\AbstractGATrackingTest;

/**
 * Class Event App Test
 *
 * @author      Marco Rieger
 * @package     Racecore\GATracking\Tracking\App
 */
class EventTest extends AbstractGATrackingTest
{
    public function testPaketEqualsSpecification()
    {
        $event = new Event();

        $event->setAppName('Test App');
        $event->setEventAction('Test Action');
        $event->setEventCategory('Test Category');

        $packet = $event->getPackage();

        $this->assertEquals(
            array(
                't' => 'event',
                'ec' => 'Test Category',
                'ea' => 'Test Action',
                'an' => 'Test App',
            ),
            $packet
        );
    }
}

<?php

namespace Racecore\GATracking\Tracking\App;

use Racecore\GATracking\AbstractGATrackingTest;

/**
 * Class ScreenTest
 *
 * @author      Marco Rieger
 * @package     Racecore\GATracking\Tracking\App
 */
class ScreenTest extends AbstractGATrackingTest
{
    public function testPaketEqualsSpecification()
    {
        $screen = new Screen();

        $screen->setAppName('Test App');
        $screen->setAppVersion('1.0');
        $screen->setContentDescription('Test Description');

        $packet = $screen->getPackage();

        $this->assertEquals(
            array(
                't' => 'appview',
                'cd' => 'Test Description',
                'an' => 'Test App',
                'av' => '1.0',
            ),
            $packet
        );
    }
}

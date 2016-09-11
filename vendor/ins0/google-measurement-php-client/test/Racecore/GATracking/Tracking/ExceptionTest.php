<?php

namespace Racecore\GATracking\Tracking;

use Racecore\GATracking\AbstractGATrackingTest;

/**
 * Class ExceptionTest
 *
 * @author      Marco Rieger
 * @package       Racecore\GATracking\Tracking
 */
class ExceptionTest extends AbstractGATrackingTest
{
    public function testPaketEqualsSpecification()
    {
        /** @var Exception $exception */
        $exception = new Exception();
        $exception->setExceptionDescription('Test Description');
        $exception->setExceptionFatal(true);

        $packet = $exception->getPackage();
        $this->assertEquals(
            array(
                't'     =>  'exception',
                'exd'    =>  'Test Description',
                'exf'    =>  '1'
            ),
            $packet
        );
    }
}

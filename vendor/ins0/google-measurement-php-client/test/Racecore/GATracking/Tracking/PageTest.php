<?php

namespace Racecore\GATracking\Tracking;

use Racecore\GATracking\AbstractGATrackingTest;

/**
 * Class PageviewTest
 *
 * @author      Marco Rieger
 * @package       Racecore\GATracking\Tracking
 */
class PageTest extends AbstractGATrackingTest
{
    public function testPaketEqualsSpecification()
    {
        /** @var Page $page */
        $page = new Page();
        $page->setDocumentPath('foo');
        $page->setDocumentTitle('bar');
        $page->setDocumentHost('baz');

        $packet = $page->getPackage();
        $this->assertEquals(
            array(
                't'     =>  'pageview',
                'dh'    =>  'baz',
                'dp'    =>  'foo',
                'dt'    =>  'bar'
            ),
            $packet
        );
    }
}

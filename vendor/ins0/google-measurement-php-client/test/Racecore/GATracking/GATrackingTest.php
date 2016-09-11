<?php

namespace Racecore\GATracking;

use Racecore\GATracking\Request\TrackingRequest;
use Racecore\GATracking\Request\TrackingRequestCollection;

/**
 * Class GATrackingTest
 *
 * @author      Marco Rieger
 * @package       Racecore\GATracking
 */
class GATrackingTest extends AbstractGATrackingTest
{
    public function setUp()
    {
        $_COOKIE = array();
    }

    public function setDown()
    {
        $_COOKIE = null;
    }

    public function testInstanceIsGenerated()
    {
        $service = new GATracking('fooId');
        $this->assertInstanceOf('Racecore\GATracking\GATracking', $service);
    }

    public function testInstanceThrowErrorWithNoAnalyticsId()
    {
        $this->setExpectedException('Racecore\GATracking\Exception\InvalidArgumentException');
        new GATracking(null);
    }

    public function testTrackingEventsCanCreatedOverMethod()
    {
        $service = new GATracking('foo');
        $tracking = $service->createTracking('Event');

        $this->assertInstanceOf('Racecore\GATracking\Tracking\Event', $tracking);
    }

    public function testAnalyticsAccountIdIsSet()
    {
        $service = new GATracking('fooId');
        $this->assertEquals('fooId', $service->getAnalyticsAccountUid());
    }

    public function testAnalyticsAccountIdCanBeSet()
    {
        $service = new GATracking('fooId');
        $service->setAnalyticsAccountUid('newFoo');
        $this->assertEquals('newFoo', $service->getAnalyticsAccountUid());
    }

    public function testClientIdCanSet()
    {
        $service = new GATracking('foo');
        $service->setClientId('foo');
        $this->assertEquals('foo', $service->getClientId());
    }

    public function testClientIdCanSetOverCookieOldUuid()
    {
        $service = new GATracking('foo');
        $_COOKIE['_ga'] = 'foo.bar.baz.quux';

        $this->assertEquals('baz.quux', $service->getClientId());
    }

    public function testClientIdCanSetOverCookieNewUuid()
    {
        $service = new GATracking('foo');
        $_COOKIE['_ga'] = 'foo.bar.f9f0b1fe-8d57-4adb-babc-787fd873613c';

        $this->assertEquals('f9f0b1fe-8d57-4adb-babc-787fd873613c', $service->getClientId());
    }

    public function testClientIdGetGenerated()
    {
        $service = new GATracking('foo');
        $service->setOption('client_create_random_id', true);

        $this->assertNotNull($service->getClientId());
    }

    public function testClientIdFallback()
    {
        $service = new GATracking('foo');
        $service->setOption('client_create_random_id', false);
        $service->setOption('client_fallback_id', 'foo');

        $this->assertEquals('foo', $service->getClientId());
    }

    public function testSetUpClientAdapterFromConstructor()
    {
        $service = new GATracking('fooId');
        $this->assertInstanceOf(
            'Racecore\GATracking\Client\AbstractClientAdapter',
            $service->getClientAdapter()
        );
    }

    public function testDefaultClientAdapterIsSet()
    {
        $service = new GATracking('foo');
        $this->assertInstanceOf('Racecore\GATracking\Client\Adapter\Socket', $service->getClientAdapter());
    }

    public function testOptionsAreMergedOverConstructor()
    {
        $service = new GATracking('foo', array(
            'foo' => 'bar',
            'baz' => array(
                'foobaz' => 'foobar'
            )
        ));
        $newOptions = $service->getOptions();

        $this->assertArrayHasKey('foo', $newOptions);
        $this->assertArrayHasKey('baz', $newOptions);
        $this->assertArrayHasKey('foobaz', $newOptions['baz']);

        $this->assertEquals('bar', $newOptions['foo']);
    }

    public function testOptionsThrowsErrorOnInvalidArg()
    {
        $this->setExpectedException('Racecore\GATracking\Exception\InvalidArgumentException');
        $service = new GATracking('foo');
        $service->setOptions('foo');
    }

    public function testOptionsCanSetOverMethod()
    {
        $service = new GATracking('foo');
        $options = array(
            'foo' => 'bar',
            'baz' => array(
                'foobaz'
            )
        );
        $service->setOptions($options);

        $this->assertEquals($options, $service->getOptions());
    }

    public function testSingleOptionCanSetOverMethod()
    {
        $service = new GATracking('foo');
        $service->setOptions(array(
            'foo' => 'bar',
            'baz' => array(
                'foobaz'
            )
        ));

        $service->setOption('foo', 'baz');
        $service->setOption('bar', 'baz');

        $newOptions = $service->getOptions();
        $this->assertEquals(array(
            'foo' => 'baz',
            'baz' => array(
                'foobaz'
            ),
            'bar' => 'baz'
        ), $newOptions);
    }

    public function testSingleArreOptionCanSetAndMergedOverMethod()
    {
        $service = new GATracking('foo');
        $service->setOptions(array(
            'foo' => 'bar',
            'baz' => array(
                'foobaz' => 'fail'
            )
        ));
        $service->setOption('baz', array('foo' => 'bar'));
        $service->setOption('baz', array('foobaz' => 'foobar'));


        $newOptions = $service->getOptions();
        $this->assertEquals(array(
            'foo' => 'bar',
            'baz' => array(
                'foobaz' => 'foobar',
                'foo' => 'bar'
            )
        ), $newOptions);
    }

    public function testOptionCanReveivedOverMethod()
    {
        $service = new GATracking('foo');
        $service->setOptions(array(
            'foo' => 'bar'
        ));

        $this->assertEquals('bar', $service->getOption('foo'));
    }

    public function testSingleTracking()
    {
        $service = new GATracking('foo');

        $trackingRequest = new TrackingRequest();
        $trackingRequest->setPayload(array('foo' => 'bar'));
        $trackingRequest->setResponseHeader(array('baz' => 'fooBar'));

        $collection = new TrackingRequestCollection();
        $collection->add($trackingRequest);

        $clientMock = $this->getMock('Racecore\GATracking\Client\Adapter\Socket');
        $clientMock->expects($this->once())
            ->method('send')
            ->will($this->returnValue($collection));

        $tracking = $this->getMock('Racecore\GATracking\Tracking\Event');
        $tracking->expects($this->once())
            ->method('getPackage')
            ->will($this->returnValue(array('test' => 'bar')));

        $service->setClientAdapter($clientMock);
        $response = $service->sendTracking($tracking);

        $responsePayload = $response->getPayload();
        $responseHeader = $response->getResponseHeader();

        $this->assertInstanceOf('Racecore\GATracking\Request\TrackingRequest', $response);
        $this->assertArrayHasKey('foo', $responsePayload);
        $this->assertEquals('bar', $responsePayload['foo']);

        $this->assertArrayHasKey('baz', $responseHeader);
        $this->assertEquals('fooBar', $responseHeader['baz']);
    }
}

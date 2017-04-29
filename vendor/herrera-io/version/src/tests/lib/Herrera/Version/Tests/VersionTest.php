<?php

namespace Herrera\Version\Tests;

use Herrera\PHPUnit\TestCase;
use Herrera\Version\Version;

class VersionTest extends TestCase
{
    /**
     * @var Version
     */
    private $version;

    public function testConstruct()
    {
        $version = new Version();

        $this->assertSame(0, $this->getPropertyValue($version, 'major'));
        $this->assertSame(0, $this->getPropertyValue($version, 'minor'));
        $this->assertSame(0, $this->getPropertyValue($version, 'patch'));
        $this->assertSame(
            array(),
            $this->getPropertyValue($version, 'preRelease')
        );
        $this->assertSame(array(), $this->getPropertyValue($version, 'build'));
    }

    /**
     * @depends testConstruct
     */
    public function testConstructWithValues()
    {
        $this->assertSame(1, $this->getPropertyValue($this->version, 'major'));
        $this->assertSame(2, $this->getPropertyValue($this->version, 'minor'));
        $this->assertSame(3, $this->getPropertyValue($this->version, 'patch'));
        $this->assertSame(
            array('pre', '1'),
            $this->getPropertyValue($this->version, 'preRelease')
        );
        $this->assertSame(
            array('build', '1'),
            $this->getPropertyValue($this->version, 'build')
        );
    }

    /**
     * @depends testConstruct
     * @depends testConstructWithValues
     */
    public function testGetBuild()
    {
        $version = new Version();

        $this->assertSame(array(), $version->getBuild());
        $this->assertSame(array('build', '1'), $this->version->getBuild());
    }

    /**
     * @depends testConstruct
     * @depends testConstructWithValues
     */
    public function testGetMajor()
    {
        $version = new Version();

        $this->assertSame(0, $version->getMajor());
        $this->assertSame(1, $this->version->getMajor());
    }

    /**
     * @depends testConstruct
     * @depends testConstructWithValues
     */
    public function testGetMinor()
    {
        $version = new Version();

        $this->assertSame(0, $version->getMinor());
        $this->assertSame(2, $this->version->getMinor());
    }

    /**
     * @depends testConstruct
     * @depends testConstructWithValues
     */
    public function testGetPatch()
    {
        $version = new Version();

        $this->assertSame(0, $version->getPatch());
        $this->assertSame(3, $this->version->getPatch());
    }

    /**
     * @depends testConstruct
     * @depends testConstructWithValues
     */
    public function testGetPreRelease()
    {
        $version = new Version();

        $this->assertSame(array(), $version->getPreRelease());
        $this->assertSame(array('pre', '1'), $this->version->getPreRelease());
    }

    public function testIsStable()
    {
        $this->assertFalse($this->version->isStable());

        $version = new Version();

        $this->assertFalse($version->isStable());

        $version = new Version(1);

        $this->assertTrue($version->isStable());
    }

    public function testToString()
    {
        $this->assertEquals('1.2.3-pre.1+build.1', (string) $this->version);
    }

    protected function setUp()
    {
        $this->version = new Version(
            1,
            2,
            3,
            array('pre', '1'),
            array('build', '1')
        );
    }
}

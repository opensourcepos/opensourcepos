<?php

namespace Herrera\Version\Tests;

use Herrera\PHPUnit\TestCase;
use Herrera\Version\Builder;
use Herrera\Version\Parser;
use Herrera\Version\Version;

class BuilderTest extends TestCase
{
    /**
     * @var Builder
     */
    private $builder;

    public function testClearBuild()
    {
        $this->setPropertyValue($this->builder, 'build', array('build', '1'));

        $this->builder->clearBuild();

        $this->assertSame(array(), $this->builder->getBuild());
    }

    public function testClearPreRelease()
    {
        $this->setPropertyValue($this->builder, 'preRelease', array('pre', '1'));

        $this->builder->clearPreRelease();

        $this->assertSame(array(), $this->builder->getPreRelease());
    }

    public function testCreate()
    {
        $this->assertInstanceOf('Herrera\\Version\\Version', Builder::create());
    }

    public function testGetVersion()
    {
        $version = $this->builder->getVersion();

        $this->assertInstanceOf('Herrera\\Version\\Version', $version);
        $this->assertSame(array(), $version->getBuild());
        $this->assertSame(0, $version->getMajor());
        $this->assertSame(0, $version->getMinor());
        $this->assertSame(0, $version->getPatch());
        $this->assertSame(array(), $version->getPreRelease());
    }

    public function testImportComponents()
    {
        $this->assertSame(
            $this->builder,
            $this->builder->importComponents(array())
        );

        $this->assertSame(array(), $this->builder->getBuild());
        $this->assertSame(0, $this->builder->getMajor());
        $this->assertSame(0, $this->builder->getMinor());
        $this->assertSame(0, $this->builder->getPatch());
        $this->assertSame(array(), $this->builder->getPreRelease());
    }

    public function testImportComponentsWithValues()
    {
        $this->assertSame(
            $this->builder,
            $this
                ->builder
                ->importComponents(
                    array(
                        Parser::MAJOR => 5,
                        Parser::MINOR => 6,
                        Parser::PATCH => 7,
                        Parser::PRE_RELEASE => array('pre', '2', '3'),
                        Parser::BUILD => array('build', '2', '3'),
                    )
                )
        );

        $this->assertSame(array('build', '2', '3'), $this->builder->getBuild());
        $this->assertSame(5, $this->builder->getMajor());
        $this->assertSame(6, $this->builder->getMinor());
        $this->assertSame(7, $this->builder->getPatch());
        $this->assertSame(
            array('pre', '2', '3'),
            $this->builder->getPreRelease()
        );
    }

    public function testImportString()
    {
        $this->assertSame(
            $this->builder,
            $this->builder->importString('5.6.7-pre.2.3+build.2.3')
        );

        $this->assertSame(array('build', '2', '3'), $this->builder->getBuild());
        $this->assertSame(5, $this->builder->getMajor());
        $this->assertSame(6, $this->builder->getMinor());
        $this->assertSame(7, $this->builder->getPatch());
        $this->assertSame(
            array('pre', '2', '3'),
            $this->builder->getPreRelease()
        );
    }

    public function testImportVersion()
    {
        $this->assertSame(
            $this->builder,
            $this->builder->importVersion(
                new Version(
                    5,
                    6,
                    7,
                    array('pre', '2', '3'),
                    array('build', '2', '3')
                )
            )
        );

        $this->assertSame(array('build', '2', '3'), $this->builder->getBuild());
        $this->assertSame(5, $this->builder->getMajor());
        $this->assertSame(6, $this->builder->getMinor());
        $this->assertSame(7, $this->builder->getPatch());
        $this->assertSame(
            array('pre', '2', '3'),
            $this->builder->getPreRelease()
        );
    }

    public function testIncrementMajor()
    {
        $this->setPropertyValue($this->builder, 'major', 1);
        $this->setPropertyValue($this->builder, 'minor', 2);
        $this->setPropertyValue($this->builder, 'patch', 3);

        $this->assertSame($this->builder, $this->builder->incrementMajor());
        $this->assertSame(2, $this->builder->getMajor());
        $this->assertSame(0, $this->builder->getMinor());
        $this->assertSame(0, $this->builder->getPatch());
    }

    public function testIncrementMajorWithValue()
    {
        $this->setPropertyValue($this->builder, 'major', 1);
        $this->setPropertyValue($this->builder, 'minor', 2);
        $this->setPropertyValue($this->builder, 'patch', 3);

        $this->assertSame($this->builder, $this->builder->incrementMajor(4));
        $this->assertSame(5, $this->builder->getMajor());
        $this->assertSame(0, $this->builder->getMinor());
        $this->assertSame(0, $this->builder->getPatch());
    }

    public function testIncrementMinor()
    {
        $this->setPropertyValue($this->builder, 'minor', 2);
        $this->setPropertyValue($this->builder, 'patch', 3);

        $this->assertSame($this->builder, $this->builder->incrementMinor());
        $this->assertSame(3, $this->builder->getMinor());
        $this->assertSame(0, $this->builder->getPatch());
    }

    public function testIncrementMinorWithValue()
    {
        $this->setPropertyValue($this->builder, 'minor', 2);
        $this->setPropertyValue($this->builder, 'patch', 3);

        $this->assertSame($this->builder, $this->builder->incrementMinor(4));
        $this->assertSame(6, $this->builder->getMinor());
        $this->assertSame(0, $this->builder->getPatch());
    }

    public function testIncrementPatch()
    {
        $this->setPropertyValue($this->builder, 'patch', 3);

        $this->assertSame($this->builder, $this->builder->incrementPatch());
        $this->assertSame(4, $this->builder->getPatch());
    }

    public function testIncrementMinorWithPatch()
    {
        $this->setPropertyValue($this->builder, 'patch', 3);

        $this->assertSame($this->builder, $this->builder->incrementPatch(4));
        $this->assertSame(7, $this->builder->getPatch());
    }

    public function testSetBuild()
    {
        $build = array('build', '1');

        $this->assertSame($this->builder, $this->builder->setBuild($build));

        $this->assertEquals($build, $this->builder->getBuild());
    }

    public function testSetBuildInvalid()
    {
        $this->setExpectedException(
            'Herrera\\Version\\Exception\\InvalidIdentifierException',
            'The identifier "+" is invalid.'
        );

        $this->builder->setBuild(array('+'));
    }

    public function testSetMajor()
    {
        $this->assertSame($this->builder, $this->builder->setMajor(123));

        $this->assertSame(123, $this->builder->getMajor());
    }

    public function testSetMajorInvalid()
    {
        $this->setExpectedException(
            'Herrera\\Version\\Exception\\InvalidNumberException',
            'The version number "x" is invalid.'
        );

        $this->builder->setMajor('x');
    }

    public function testSetMinor()
    {
        $this->assertSame($this->builder, $this->builder->setMinor(123));

        $this->assertSame(123, $this->builder->getMinor());
    }

    public function testSetMinorInvalid()
    {
        $this->setExpectedException(
            'Herrera\\Version\\Exception\\InvalidNumberException',
            'The version number "x" is invalid.'
        );

        $this->builder->setMinor('x');
    }

    public function testSetPatch()
    {
        $this->assertSame($this->builder, $this->builder->setPatch(123));

        $this->assertSame(123, $this->builder->getPatch());
    }

    public function testSetPatchInvalid()
    {
        $this->setExpectedException(
            'Herrera\\Version\\Exception\\InvalidNumberException',
            'The version number "x" is invalid.'
        );

        $this->builder->setPatch('x');
    }

    public function testSetPreRelease()
    {
        $pre = array('pre', '1');

        $this->assertSame($this->builder, $this->builder->setPreRelease($pre));

        $this->assertEquals($pre, $this->builder->getPreRelease());
    }

    public function testSetPreReleaseInvalid()
    {
        $this->setExpectedException(
            'Herrera\\Version\\Exception\\InvalidIdentifierException',
            'The identifier "+" is invalid.'
        );

        $this->builder->setPreRelease(array('+'));
    }

    protected function setUp()
    {
        $this->builder = new Builder();
    }
}

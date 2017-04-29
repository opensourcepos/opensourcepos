<?php

namespace Herrera\Version\Tests;

use Herrera\PHPUnit\TestCase;
use Herrera\Version\Parser;

class ParserTest extends TestCase
{
    public function testToBuilder()
    {
        $builder = Parser::toBuilder('1.2.3-pre.1+build.1');

        $this->assertSame(1, $builder->getMajor());
        $this->assertSame(2, $builder->getMinor());
        $this->assertSame(3, $builder->getPatch());
        $this->assertSame(array('pre', '1'), $builder->getPreRelease());
        $this->assertSame(array('build', '1'), $builder->getBuild());
    }

    public function testToComponents()
    {
        $this->assertSame(
            array(
                Parser::MAJOR => 1,
                Parser::MINOR => 2,
                Parser::PATCH => 3,
                Parser::PRE_RELEASE => array('pre', '1'),
                Parser::BUILD => array('build', '1')
            ),
            Parser::toComponents('1.2.3-pre.1+build.1')
        );
    }

    public function testToComponentsInvalid()
    {
        $this->setExpectedException(
            'Herrera\\Version\\Exception\\InvalidStringRepresentationException',
            'The version string representation "test" is invalid.'
        );

        Parser::toComponents('test');
    }

    public function testToVersion()
    {
        $version = Parser::toVersion('1.2.3-pre.1+build.1');

        $this->assertSame(1, $version->getMajor());
        $this->assertSame(2, $version->getMinor());
        $this->assertSame(3, $version->getPatch());
        $this->assertSame(array('pre', '1'), $version->getPreRelease());
        $this->assertSame(array('build', '1'), $version->getBuild());
    }
}

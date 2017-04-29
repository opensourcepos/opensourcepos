<?php

namespace Herrera\Version\Tests;

use Herrera\PHPUnit\TestCase;
use Herrera\Version\Builder;
use Herrera\Version\Comparator;

class ComparatorTest extends TestCase
{
    public function getAll()
    {
        return array_merge(
            $this->getEqual(),
            $this->getGreater(),
            $this->getLess()
        );
    }

    public function getEqual()
    {
        return array(
            array('0.0.0', '0.0.0', Comparator::EQUAL_TO),
            array('0.0.1', '0.0.1', Comparator::EQUAL_TO),
            array('0.1.0', '0.1.0', Comparator::EQUAL_TO),
            array('1.0.0', '1.0.0', Comparator::EQUAL_TO),
            array('1.1.1', '1.1.1', Comparator::EQUAL_TO),

            array('0.0.0-0', '0.0.0-0', Comparator::EQUAL_TO),
            array('0.0.0+0', '0.0.0+1', Comparator::EQUAL_TO),
        );
    }

    public function getGreater()
    {
        return array(
            array('0.0.2', '0.0.1', Comparator::GREATER_THAN),
            array('0.2.0', '0.1.0', Comparator::GREATER_THAN),
            array('2.0.0', '1.0.0', Comparator::GREATER_THAN),

            array('0.0.0', '0.0.0-0', Comparator::GREATER_THAN),
            array('0.0.0-2', '0.0.0-1', Comparator::GREATER_THAN),
            array('0.0.0-a', '0.0.0-3', Comparator::GREATER_THAN),
            array('0.0.0-b', '0.0.0-a', Comparator::GREATER_THAN),

            array('0.0.0-a.b.c', '0.0.0-a.1', Comparator::GREATER_THAN),
            array('0.0.0-1.2.b', '0.0.0-1.2', Comparator::GREATER_THAN),

            array('0.0.0-rc', '0.0.0-beta', Comparator::GREATER_THAN),
            array('0.0.0-beta', '0.0.0-alpha', Comparator::GREATER_THAN),

            // semver.org precedence expectations
            array('1.0.0', '1.0.0-rc.1', Comparator::GREATER_THAN),
            array('1.0.0-rc.1', '1.0.0-beta.11', Comparator::GREATER_THAN),
            array('1.0.0-beta.11', '1.0.0-beta.2', Comparator::GREATER_THAN),
            array('1.0.0-beta.2', '1.0.0-beta', Comparator::GREATER_THAN),
            array('1.0.0-beta', '1.0.0-alpha.beta', Comparator::GREATER_THAN),
            array('1.0.0-alpha.beta', '1.0.0-alpha.1', Comparator::GREATER_THAN),
            array('1.0.0-alpha.1', '1.0.0-alpha', Comparator::GREATER_THAN),
        );
    }

    public function getLess()
    {
        return array(
            array('0.0.1', '0.0.2', Comparator::LESS_THAN),
            array('0.1.0', '0.2.0', Comparator::LESS_THAN),
            array('1.0.0', '2.0.0', Comparator::LESS_THAN),

            array('0.0.0-0', '0.0.0', Comparator::LESS_THAN),
            array('0.0.0-1', '0.0.0-2', Comparator::LESS_THAN),
            array('0.0.0-3', '0.0.0-a', Comparator::LESS_THAN),
            array('0.0.0-a', '0.0.0-b', Comparator::LESS_THAN),

            array('0.0.0-a.1', '0.0.0-a.b.c', Comparator::LESS_THAN),
            array('0.0.0-1.2', '0.0.0-1.2.b', Comparator::LESS_THAN),

            array('0.0.0-alpha', '0.0.0-beta', Comparator::LESS_THAN),
            array('0.0.0-beta', '0.0.0-rc', Comparator::LESS_THAN),

            // semver.org precedence expectations
            array('1.0.0-alpha', '1.0.0-alpha.1', Comparator::LESS_THAN),
            array('1.0.0-alpha.1', '1.0.0-alpha.beta', Comparator::LESS_THAN),
            array('1.0.0-alpha.beta', '1.0.0-beta', Comparator::LESS_THAN),
            array('1.0.0-beta', '1.0.0-beta.2', Comparator::LESS_THAN),
            array('1.0.0-beta.2', '1.0.0-beta.11', Comparator::LESS_THAN),
            array('1.0.0-beta.11', '1.0.0-rc.1', Comparator::LESS_THAN),
            array('1.0.0-rc.1', '1.0.0', Comparator::LESS_THAN),
        );
    }

    /**
     * @dataProvider getAll
     */
    public function testCompareTo($left, $right, $expected)
    {
        $this->assertSame(
            $expected,
            Comparator::compareTo(
                Builder::create()->importString($left),
                Builder::create()->importString($right)
            )
        );
    }

    /**
     * @dataProvider getEqual
     */
    public function testIsEqualTo($left, $right)
    {
        $this->assertTrue(
            Comparator::isEqualTo(
                Builder::create()->importString($left),
                Builder::create()->importString($right)
            )
        );
    }

    /**
     * @dataProvider getGreater
     */
    public function testIsGreaterThan($left, $right)
    {
        $this->assertTrue(
            Comparator::isGreaterThan(
                Builder::create()->importString($left),
                Builder::create()->importString($right)
            )
        );
    }

    /**
     * @dataProvider getLess
     */
    public function testIsLessThan($left, $right)
    {
        $this->assertTrue(
            Comparator::isLessThan(
                Builder::create()->importString($left),
                Builder::create()->importString($right)
            )
        );
    }
}

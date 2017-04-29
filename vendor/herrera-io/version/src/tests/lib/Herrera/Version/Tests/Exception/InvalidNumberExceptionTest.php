<?php

namespace Herrera\Version\Tests\Exception;

use Herrera\PHPUnit\TestCase;
use Herrera\Version\Exception\InvalidNumberException;

class InvalidNumberExceptionTest extends TestCase
{
    public function testConstruct()
    {
        $exception = new InvalidNumberException('test');

        $this->assertEquals(
            'The version number "test" is invalid.',
            $exception->getMessage()
        );
    }

    /**
     * @depends testConstruct
     */
    public function testGetNumber()
    {
        $exception = new InvalidNumberException('test');

        $this->assertEquals('test', $exception->getNumber());
    }
}

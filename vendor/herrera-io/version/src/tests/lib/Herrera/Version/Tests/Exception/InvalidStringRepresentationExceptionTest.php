<?php

namespace Herrera\Version\Tests\Exception;

use Herrera\PHPUnit\TestCase;
use Herrera\Version\Exception\InvalidStringRepresentationException;

class InvalidStringRepresentationExceptionTest extends TestCase
{
    public function testConstruct()
    {
        $exception = new InvalidStringRepresentationException('test');

        $this->assertEquals(
            'The version string representation "test" is invalid.',
            $exception->getMessage()
        );
    }

    /**
     * @depends testConstruct
     */
    public function testGetStringRepresentation()
    {
        $exception = new InvalidStringRepresentationException('test');

        $this->assertEquals('test', $exception->getVersion());
    }
}

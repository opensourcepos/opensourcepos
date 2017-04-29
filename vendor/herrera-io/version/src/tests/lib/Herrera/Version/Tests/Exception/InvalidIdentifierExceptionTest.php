<?php

namespace Herrera\Version\Tests\Exception;

use Herrera\PHPUnit\TestCase;
use Herrera\Version\Exception\InvalidIdentifierException;

class InvalidIdentifierExceptionTest extends TestCase
{
    public function testConstruct()
    {
        $exception = new InvalidIdentifierException('test');

        $this->assertEquals(
            'The identifier "test" is invalid.',
            $exception->getMessage()
        );
    }

    /**
     * @depends testConstruct
     */
    public function testGetIdentifier()
    {
        $exception = new InvalidIdentifierException('test');

        $this->assertEquals('test', $exception->getIdentifier());
    }
}

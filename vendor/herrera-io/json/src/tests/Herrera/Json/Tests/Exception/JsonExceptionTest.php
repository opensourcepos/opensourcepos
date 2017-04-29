<?php

namespace Herrera\Json\Tests\Exception;

use Herrera\Json\Exception\JsonException;
use Herrera\PHPUnit\TestCase;

class JsonExceptionTest extends TestCase
{
    public function testConstruct()
    {
        $errors = array('Another error.');
        $exception = new JsonException('My message.', $errors);

        $this->assertEquals('My message.', $exception->getMessage());
        $this->assertEquals($errors, $exception->getErrors());
    }

    public function testCreateUsingCode()
    {
        $exception = JsonException::createUsingCode(JSON_ERROR_NONE);

        $this->assertEquals(
            'No error has occurred.',
            $exception->getMessage()
        );
    }
}

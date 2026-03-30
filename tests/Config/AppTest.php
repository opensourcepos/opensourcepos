<?php

namespace Tests\Config;

use CodeIgniter\Test\CIUnitTestCase;
use Config\App;

class AppTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testGetValidHostReturnsHostWhenValid(): void
    {
        $app = new class extends App {
            public array $allowedHostnames = ['example.com', 'www.example.com'];
            
            public function __construct() {}
        };

        $reflection = new \ReflectionClass($app);
        $method = $reflection->getMethod('getValidHost');
        $method->setAccessible(true);

        $_SERVER['HTTP_HOST'] = 'example.com';
        $host = $method->invoke($app);
        $this->assertEquals('example.com', $host);

        $_SERVER['HTTP_HOST'] = 'www.example.com';
        $host = $method->invoke($app);
        $this->assertEquals('www.example.com', $host);
    }

    public function testGetValidHostReturnsFallbackForInvalidHost(): void
    {
        $app = new class extends App {
            public array $allowedHostnames = ['example.com', 'www.example.com'];
            
            public function __construct() {}
        };

        $reflection = new \ReflectionClass($app);
        $method = $reflection->getMethod('getValidHost');
        $method->setAccessible(true);

        $_SERVER['HTTP_HOST'] = 'malicious.com';
        $host = $method->invoke($app);
        $this->assertEquals('example.com', $host);

        $_SERVER['HTTP_HOST'] = 'evil.org';
        $host = $method->invoke($app);
        $this->assertEquals('example.com', $host);
    }

    public function testGetValidHostReturnsLocalhostWhenNoWhitelist(): void
    {
        $app = new class extends App {
            public array $allowedHostnames = [];
            
            public function __construct() {}
        };

        $reflection = new \ReflectionClass($app);
        $method = $reflection->getMethod('getValidHost');
        $method->setAccessible(true);

        $_SERVER['HTTP_HOST'] = 'malicious.com';
        $host = $method->invoke($app);
        $this->assertEquals('localhost', $host);

        $_SERVER['HTTP_HOST'] = 'example.com';
        $host = $method->invoke($app);
        $this->assertEquals('localhost', $host);
    }

    public function testGetValidHostHandlesMissingHttpHost(): void
    {
        $app = new class extends App {
            public array $allowedHostnames = ['example.com'];
            
            public function __construct() {}
        };

        $reflection = new \ReflectionClass($app);
        $method = $reflection->getMethod('getValidHost');
        $method->setAccessible(true);

        unset($_SERVER['HTTP_HOST']);
        $host = $method->invoke($app);
        $this->assertEquals('example.com', $host);
    }

    public function testBaseURLContainsValidHost(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['HTTPS'] = null;

        $app = new class extends App {
            public array $allowedHostnames = ['example.com'];
        };

        $this->assertStringContainsString('example.com', $app->baseURL);
    }

    public function testBaseURLUsesFallbackHostWhenInvalidHostProvided(): void
    {
        $_SERVER['HTTP_HOST'] = 'malicious.com';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['HTTPS'] = null;

        $app = new class extends App {
            public array $allowedHostnames = ['example.com'];
        };

        $this->assertStringContainsString('example.com', $app->baseURL);
        $this->assertStringNotContainsString('malicious.com', $app->baseURL);
    }
}
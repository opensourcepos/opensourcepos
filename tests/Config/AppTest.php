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
        // Clean up environment
        putenv('CI_ENVIRONMENT');
        putenv('app.allowedHostnames');
        unset($_SERVER['HTTP_HOST']);
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

    public function testGetValidHostReturnsLocalhostInDevelopmentWhenNoWhitelist(): void
    {
        // Set development environment
        putenv('CI_ENVIRONMENT=development');

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

    public function testGetValidHostThrowsExceptionInProductionWhenNoWhitelist(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('allowedHostnames is not configured');

        // Set production environment
        putenv('CI_ENVIRONMENT=production');

        $app = new class extends App {
            public array $allowedHostnames = [];
            
            public function __construct() {}
        };

        $reflection = new \ReflectionClass($app);
        $method = $reflection->getMethod('getValidHost');
        $method->setAccessible(true);

        $_SERVER['HTTP_HOST'] = 'malicious.com';
        $method->invoke($app);
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

    public function testEnvAllowedHostnamesParsedAsCommaSeparated(): void
    {
        // Set environment variable
        putenv('app.allowedHostnames=example.com,www.example.com,demo.example.com');

        $_SERVER['HTTP_HOST'] = 'www.example.com';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['HTTPS'] = null;

        $app = new App();

        // Constructor should parse comma-separated values
        $this->assertEquals(['example.com', 'www.example.com', 'demo.example.com'], $app->allowedHostnames);
        $this->assertStringContainsString('www.example.com', $app->baseURL);

        // Clean up
        putenv('app.allowedHostnames');
    }

    public function testEnvAllowedHostnamesTrimmedWhitespace(): void
    {
        // Set environment variable with whitespace
        putenv('app.allowedHostnames= example.com , www.example.com , demo.example.com ');

        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['HTTPS'] = null;

        $app = new App();

        // Values should be trimmed
        $this->assertEquals(['example.com', 'www.example.com', 'demo.example.com'], $app->allowedHostnames);

        // Clean up
        putenv('app.allowedHostnames');
    }

    public function testEnvAllowedHostnamesSingleValue(): void
    {
        // Set environment variable with single value
        putenv('app.allowedHostnames=localhost');

        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['HTTPS'] = null;

        $app = new App();

        // Single value should work
        $this->assertEquals(['localhost'], $app->allowedHostnames);
        $this->assertStringContainsString('localhost', $app->baseURL);

        // Clean up
        putenv('app.allowedHostnames');
    }

    public function testEnvAllowedHostnamesEmptyStringNotConfigured(): void
    {
        // Set environment variable to empty string
        putenv('app.allowedHostnames=');

        // Set development environment
        putenv('CI_ENVIRONMENT=development');

        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['HTTPS'] = null;

        $app = new App();

        // Empty string should be treated as not configured
        $this->assertEquals([], $app->allowedHostnames);

        // In development, should fall back to localhost
        $this->assertStringContainsString('localhost', $app->baseURL);

        // Clean up
        putenv('app.allowedHostnames');
        putenv('CI_ENVIRONMENT');
    }

    public function testEnvAllowedHostnamesFiltersEmptyEntries(): void
    {
        // Trailing comma should not produce empty entry
        putenv('app.allowedHostnames=example.com,');
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['HTTPS'] = null;

        $app = new App();
        $this->assertEquals(['example.com'], $app->allowedHostnames);

        // Clean up
        putenv('app.allowedHostnames');

        // Leading comma should not produce empty entry
        putenv('app.allowedHostnames=,example.com');
        $_SERVER['HTTP_HOST'] = 'example.com';

        $app = new App();
        $this->assertEquals(['example.com'], $app->allowedHostnames);

        // Clean up
        putenv('app.allowedHostnames');

        // Whitespace-only entry should be filtered
        putenv('app.allowedHostnames=example.com, ,www.example.com');
        $_SERVER['HTTP_HOST'] = 'example.com';

        $app = new App();
        $this->assertEquals(['example.com', 'www.example.com'], $app->allowedHostnames);

        // Clean up
        putenv('app.allowedHostnames');

        // All-whitespace value should be treated as not configured
        putenv('CI_ENVIRONMENT=development');
        putenv('app.allowedHostnames= , , ');
        $_SERVER['HTTP_HOST'] = 'example.com';

        $app = new App();
        $this->assertEquals([], $app->allowedHostnames);
        $this->assertStringContainsString('localhost', $app->baseURL);

        // Clean up
        putenv('app.allowedHostnames');
        putenv('CI_ENVIRONMENT');
    }
}
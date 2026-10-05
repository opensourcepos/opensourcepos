<?php

namespace Tests\Events;

use App\Events\Load_config;
use CodeIgniter\Test\CIUnitTestCase;
use ReflectionMethod;

class Load_configTest extends CIUnitTestCase
{
    private function languageExists(string $languageCode): bool
    {
        $instance = new Load_config();
        $method   = new ReflectionMethod(Load_config::class, 'languageExists');
        $method->setAccessible(true);

        return (bool) $method->invoke($instance, $languageCode);
    }

    public function testLanguageExistsAcceptsValidCode(): void
    {
        $this->assertTrue($this->languageExists('en'));
    }

    public function testLanguageExistsRejectsNullByte(): void
    {
        $this->assertFalse($this->languageExists("en\0"));
    }

    public function testLanguageExistsRejectsNullByteOnly(): void
    {
        $this->assertFalse($this->languageExists("\0"));
    }

    public function testLanguageExistsRejectsForwardSlashPath(): void
    {
        $this->assertFalse($this->languageExists('en/../../etc/passwd'));
    }

    public function testLanguageExistsRejectsBackslashPath(): void
    {
        $this->assertFalse($this->languageExists('..\..\etc\passwd'));
    }

    public function testLanguageExistsRejectsParentDir(): void
    {
        $this->assertFalse($this->languageExists('..'));
    }
}

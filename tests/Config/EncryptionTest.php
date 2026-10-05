<?php

namespace Tests\Config;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Encryption;

/**
 * Pure-function tests for the app Encryption config's key helpers
 * (resolveKey() / parseKey()) — no global env mutation, so no cross-test leaks.
 */
class EncryptionTest extends CIUnitTestCase
{
    public function testHighestPrecedenceNonEmptySourceWins(): void
    {
        $this->assertSame(
            'server-key',
            Encryption::resolveKey('server-key', 'env-key', 'getenv-key', 'docker-key')
        );
    }

    public function testEmptyStringDoesNotShadowLaterSource(): void
    {
        // A `??`-based lookup would stop at a higher-precedence empty string;
        // the cascade must skip empties and reach the real key.
        $this->assertSame(
            'getenv-key',
            Encryption::resolveKey('', '', 'getenv-key', 'docker-key')
        );

        $this->assertSame(
            'docker-key',
            Encryption::resolveKey('', '', '', 'docker-key')
        );
    }

    public function testAllEmptySourcesReturnEmptyString(): void
    {
        $this->assertSame('', Encryption::resolveKey('', '', '', ''));
    }

    public function testPrefixedFallbackKeyIsDecoded(): void
    {
        // Fallback-selected keys (notably ENCRYPTION_KEY, which BaseConfig
        // never reads) must be decoded like BaseConfig does, so a
        // hex2bin:/base64:-prefixed value is not stored verbatim.
        $this->assertSame("\xab\xcd", Encryption::parseKey('hex2bin:abcd'));
        $this->assertSame("\x68\x65\x6c\x6c\x6f", Encryption::parseKey('base64:aGVsbG8='));

        // No prefix / empty value passes through unchanged.
        $this->assertSame('plain-key', Encryption::parseKey('plain-key'));
        $this->assertSame('', Encryption::parseKey(''));
    }
}

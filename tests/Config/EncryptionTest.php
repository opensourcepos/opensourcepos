<?php

namespace Tests\Config;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Encryption;

/**
 * Tests for the app Encryption config's key-resolution logic
 * (Config\Encryption::resolveKey()).
 *
 * These are pure-function tests: they exercise the resolution rules directly
 * without mutating global environment state, so they cannot leak across the
 * rest of the suite.
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
        // Regression for the PR #4714 CodeRabbit finding: a `??`-based lookup
        // would stop at an empty string from a higher-precedence source and
        // return ''. The cascade must skip empties and reach the real key.
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
        // Regression: a key selected in the constructor fallback (notably
        // `ENCRYPTION_KEY`, which BaseConfig never inspects) must be
        // decode-parsed exactly like BaseConfig::parseEncryptionKey() does for
        // `encryption.key`, so a `hex2bin:`/`base64:`-prefixed value is not
        // assigned verbatim (which would break decryption of existing
        // ciphertext).
        $this->assertSame("\xab\xcd", Encryption::parseKey('hex2bin:abcd'));
        $this->assertSame("\x68\x65\x6c\x6c\x6f", Encryption::parseKey('base64:aGVsbG8='));

        // No prefix / empty value passes through unchanged.
        $this->assertSame('plain-key', Encryption::parseKey('plain-key'));
        $this->assertSame('', Encryption::parseKey(''));
    }
}

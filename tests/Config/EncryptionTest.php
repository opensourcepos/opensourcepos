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
}

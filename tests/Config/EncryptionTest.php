<?php

namespace Tests\Config;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Encryption;

/**
 * Tests for the app Encryption config's key resolution.
 *
 * The constructor resolves the key from the provisioned `encryption.key`
 * source (read in precedence order: $_SERVER, $_ENV, then getenv()) before
 * falling back to the Docker `ENCRYPTION_KEY` env var.
 */
class EncryptionTest extends CIUnitTestCase
{
    /**
     * Regression test for CodeRabbit finding on PR #4714 (app/Config/Encryption.php):
     * when the higher-precedence sources ($_SERVER / $_ENV) hold an EMPTY string
     * but the real provisioned key lives in the process env (getenv()), the key
     * must still be picked up. A `??`-based lookup would stop at the empty string.
     */
    public function testKeyFallsBackToGetenvWhenHigherSourcesAreEmptyStrings(): void
    {
        $prevServer = $_SERVER['encryption.key'] ?? null;
        $prevEnv    = $_ENV['encryption.key'] ?? null;

        putenv('ENCRYPTION_KEY'); // ensure the Docker fallback cannot mask this path
        putenv('encryption.key=provisioned-real-key');
        $_SERVER['encryption.key'] = '';
        $_ENV['encryption.key']    = '';

        try {
            $config = new Encryption();
            $this->assertSame('provisioned-real-key', $config->key);
        } finally {
            if ($prevServer === null) {
                unset($_SERVER['encryption.key']);
            } else {
                $_SERVER['encryption.key'] = $prevServer;
            }
            if ($prevEnv === null) {
                unset($_ENV['encryption.key']);
            } else {
                $_ENV['encryption.key'] = $prevEnv;
            }
            putenv('encryption.key');
            putenv('ENCRYPTION_KEY');
        }
    }

    public function testNonEmptyServerEntryTakesPrecedence(): void
    {
        $prevServer = $_SERVER['encryption.key'] ?? null;
        $prevEnv    = $_ENV['encryption.key'] ?? null;

        putenv('ENCRYPTION_KEY');
        putenv('encryption.key=getenv-key');
        $_SERVER['encryption.key'] = 'server-key';
        $_ENV['encryption.key']    = 'env-key';

        try {
            $config = new Encryption();
            $this->assertSame('server-key', $config->key);
        } finally {
            if ($prevServer === null) {
                unset($_SERVER['encryption.key']);
            } else {
                $_SERVER['encryption.key'] = $prevServer;
            }
            if ($prevEnv === null) {
                unset($_ENV['encryption.key']);
            } else {
                $_ENV['encryption.key'] = $prevEnv;
            }
            putenv('encryption.key');
            putenv('ENCRYPTION_KEY');
        }
    }

    public function testFallsBackToEncryptionKeyEnvVarWhenNoProvisionedKey(): void
    {
        $prevServer = $_SERVER['encryption.key'] ?? null;
        $prevEnv    = $_ENV['encryption.key'] ?? null;

        putenv('encryption.key'); // no provisioned key in the process env
        $_SERVER['encryption.key'] = '';
        $_ENV['encryption.key']    = '';
        putenv('ENCRYPTION_KEY=docker-key');

        try {
            $config = new Encryption();
            $this->assertSame('docker-key', $config->key);
        } finally {
            if ($prevServer === null) {
                unset($_SERVER['encryption.key']);
            } else {
                $_SERVER['encryption.key'] = $prevServer;
            }
            if ($prevEnv === null) {
                unset($_ENV['encryption.key']);
            } else {
                $_ENV['encryption.key'] = $prevEnv;
            }
            putenv('encryption.key');
            putenv('ENCRYPTION_KEY');
        }
    }
}

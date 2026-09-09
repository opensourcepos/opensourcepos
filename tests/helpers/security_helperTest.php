<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * ROOTPATH/WRITEPATH are hard-defined constants that can't be redirected in
 * tests, so the filesystem-touching tests below operate on the project's
 * real .env and writable/backup/.env.bak. setUp()/tearDown() capture and
 * restore both files (and config('Encryption')->key and throttle.key) around
 * every test — do not remove those safeguards.
 */
class security_helperTest extends CIUnitTestCase
{
    private string $envPath;
    private string $backupPath;
    private string $lockPath;
    private ?string $envContentsBefore;
    private ?string $backupContentsBefore;
    private string $encryptionKeyBefore;
    private ?string $throttleKeyBefore;
    private bool $hadThrottleServer = false;
    private ?string $throttleServerBefore = null;

    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/../../app/Helpers/security_helper.php';

        $this->envPath    = ROOTPATH . '.env';
        $this->backupPath = WRITEPATH . '/backup/.env.bak';
        $this->lockPath   = ROOTPATH . '.env.lock';

        $this->envContentsBefore    = file_exists($this->envPath) ? file_get_contents($this->envPath) : null;
        $this->backupContentsBefore = file_exists($this->backupPath) ? file_get_contents($this->backupPath) : null;
        $this->encryptionKeyBefore  = (string) config('Encryption')->key;
        $this->throttleKeyBefore   = (string) env('throttle.key', '');
        $this->hadThrottleServer   = array_key_exists('throttle.key', $_SERVER);
        $this->throttleServerBefore = $this->hadThrottleServer ? (string) $_SERVER['throttle.key'] : null;
    }

    protected function tearDown(): void
    {
        if ($this->envContentsBefore === null) {
            @unlink($this->envPath);
        } else {
            file_put_contents($this->envPath, $this->envContentsBefore);
        }

        if ($this->backupContentsBefore === null) {
            @unlink($this->backupPath);
        } else {
            file_put_contents($this->backupPath, $this->backupContentsBefore);
        }

        @unlink($this->lockPath);
        foreach (glob(ROOTPATH . '.env.tmp.*') as $stray) {
            @unlink($stray);
        }

        config('Encryption')->key = $this->encryptionKeyBefore;

        $this->restoreThrottleKey();

        parent::tearDown();
    }

    private function restoreThrottleKey(): void
    {
        putenv('throttle.key');
        unset($_ENV['throttle.key'], $_SERVER['throttle.key']);

        if ($this->throttleKeyBefore !== '') {
            putenv("throttle.key={$this->throttleKeyBefore}");
            $_ENV['throttle.key']     = $this->throttleKeyBefore;
            $_SERVER['throttle.key']  = $this->throttleKeyBefore;
        } elseif ($this->hadThrottleServer) {
            $_SERVER['throttle.key']  = $this->throttleServerBefore;
        }
    }

    // -- applyEnvKeyReplacement() — pure function, no I/O --

    public function testApplyEnvKeyReplacementReplacesExistingKey(): void
    {
        $configFile = "foo='bar'\nencryption.key='old'\nbaz='qux'\n";

        $result = applyEnvKeyReplacement($configFile, 'encryption.key', 'new');

        $this->assertSame("foo='bar'\nencryption.key='new'\nbaz='qux'\n", $result);
    }

    public function testApplyEnvKeyReplacementInsertsAfterEncryptionKey(): void
    {
        $configFile = "encryption.key='abc'\nfoo='bar'\n";

        $result = applyEnvKeyReplacement($configFile, 'throttle.key', 'xyz');

        $this->assertSame("encryption.key='abc'\nthrottle.key='xyz'\nfoo='bar'\n", $result);
    }

    public function testApplyEnvKeyReplacementAppendsWhenNoEncryptionKey(): void
    {
        $configFile = "foo='bar'\n";

        $result = applyEnvKeyReplacement($configFile, 'throttle.key', 'xyz');

        $this->assertSame("foo='bar'\n\nthrottle.key='xyz'\n", $result);
    }

    public function testApplyEnvKeyReplacementEscapesBackslashAndDollar(): void
    {
        $configFile = "encryption.key='old'\n";

        $result = applyEnvKeyReplacement($configFile, 'encryption.key', 'a\\b$c');

        $this->assertSame("encryption.key='a\\\\b\\\$c'\n", $result);
    }

    public function testApplyEnvKeyReplacementInsertEscapesBackslashAndDollar(): void
    {
        $configFile = "encryption.key='abc'\nfoo='bar'\n";

        $result = applyEnvKeyReplacement($configFile, 'throttle.key', 'a\\b$c');

        $this->assertSame("encryption.key='abc'\nthrottle.key='a\\\\b\\\$c'\nfoo='bar'\n", $result);
    }

    public function testApplyEnvKeyReplacementAppendEscapesBackslashAndDollar(): void
    {
        $configFile = "foo='bar'\n";

        $result = applyEnvKeyReplacement($configFile, 'throttle.key', 'a\\b$c');

        $this->assertSame("foo='bar'\n\nthrottle.key='a\\\\b\\\$c'\n", $result);
    }

    // -- writeEnvKey() / atomicWriteFile() — real filesystem --

    public function testWriteEnvKeyReplacesExistingKeyOnDisk(): void
    {
        file_put_contents($this->envPath, "encryption.key='old'\nfoo='bar'\n");

        $result = writeEnvKey('encryption.key', 'newvalue');

        $this->assertTrue($result);
        $this->assertStringContainsString("encryption.key='newvalue'", file_get_contents($this->envPath));
    }

    public function testWriteEnvKeyInsertsNewKeyOnDisk(): void
    {
        file_put_contents($this->envPath, "encryption.key='abc'\n");

        $result = writeEnvKey('throttle.key', 'newvalue');

        $this->assertTrue($result);
        $this->assertStringContainsString("throttle.key='newvalue'", file_get_contents($this->envPath));
    }

    public function testAtomicWriteFileWritesContentsAndLeavesNoTempFile(): void
    {
        $result = atomicWriteFile($this->envPath, 'hello world');

        $this->assertTrue($result);
        $this->assertSame('hello world', file_get_contents($this->envPath));
        $this->assertSame([], glob(ROOTPATH . '.env.tmp.*'));
    }

    // -- checkEncryption() — READ-ONLY guard --

    public function testCheckEncryptionPassesWhenKeyValid(): void
    {
        $validKey = bin2hex(random_bytes(32));
        config('Encryption')->key = $validKey;
        file_put_contents($this->envPath, "encryption.key='$validKey'\n");

        $result = checkEncryption();

        $this->assertTrue($result);
        $this->assertSame($validKey, config('Encryption')->key);
    }

    public function testCheckEncryptionThrowsWhenKeyEmpty(): void
    {
        config('Encryption')->key = '';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('provisioned');

        // Guard must NOT write, only throw.
        $this->assertNull(@checkEncryption());
    }

    public function testCheckEncryptionThrowsWhenKeyTooShort(): void
    {
        config('Encryption')->key = 'tooshort';
        file_put_contents($this->envPath, "encryption.key='tooshort'\n");

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('provisioned');

        @checkEncryption();
    }

    // -- checkThrottleEncryption() — READ-ONLY guard --

    public function testCheckThrottleEncryptionReturnsWhenKeyPresent(): void
    {
        $key = bin2hex(random_bytes(32));
        putenv("throttle.key=$key");
        $_ENV['throttle.key']   = $key;
        $_SERVER['throttle.key'] = $key;

        $result = checkThrottleEncryption();

        $this->assertSame($key, $result);
    }

    public function testCheckThrottleEncryptionThrowsWhenKeyMissing(): void
    {
        putenv('throttle.key');
        unset($_ENV['throttle.key'], $_SERVER['throttle.key']);
        file_put_contents($this->envPath, "encryption.key='abc'\n");

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('provisioned');

        @checkThrottleEncryption();
    }

    // -- rotateEncryptionKey() — provisioning path, does write --

    public function testRotateEncryptionKeyPersistsNewKey(): void
    {
        file_put_contents($this->envPath, "encryption.key='tooshort'\n");
        config('Encryption')->key = 'tooshort';

        $newKey = rotateEncryptionKey('tooshort');

        $this->assertNotSame('tooshort', $newKey);
        $this->assertGreaterThanOrEqual(64, strlen($newKey));
        $this->assertSame($newKey, config('Encryption')->key);
        $this->assertStringContainsString("encryption.key='$newKey'", file_get_contents($this->envPath));
    }

    public function testRotateEncryptionKeyLeavesOldKeyAsCommentedBackup(): void
    {
        file_put_contents($this->envPath, "encryption.key='tooshort'\n");
        config('Encryption')->key = 'tooshort';

        rotateEncryptionKey('tooshort');

        $this->assertStringContainsString("# encryption.key='tooshort'", file_get_contents($this->envPath));
    }

    public function testRotateEncryptionKeyNoBackupWhenOldKeyEmpty(): void
    {
        file_put_contents($this->envPath, "# blank\n");
        config('Encryption')->key = '';

        $newKey = rotateEncryptionKey(null);

        $this->assertGreaterThanOrEqual(64, strlen($newKey));
        $this->assertStringContainsString("encryption.key='$newKey'", file_get_contents($this->envPath));
        $this->assertStringNotContainsString("# encryption.key=''", file_get_contents($this->envPath));
    }

    public function testRotateEncryptionKeyThrowsWhenEnvPathIsDirectory(): void
    {
        @unlink($this->envPath);
        mkdir($this->envPath);

        try {
            $this->expectException(RuntimeException::class);
            rotateEncryptionKey(null);
        } finally {
            @rmdir($this->envPath);
        }
    }

    // -- provisionThrottleKey() — idempotent, does write --

    public function testProvisionThrottleKeyGeneratesAndPersistsWhenMissing(): void
    {
        putenv('throttle.key');
        unset($_ENV['throttle.key'], $_SERVER['throttle.key']);
        file_put_contents($this->envPath, "encryption.key='abc'\n");

        $key = provisionThrottleKey();

        $this->assertNotSame('', $key);
        $this->assertStringContainsString("throttle.key='$key'", file_get_contents($this->envPath));
        $this->assertSame($key, $_ENV['throttle.key'] ?? '');
    }

    public function testProvisionThrottleKeyIdempotentWhenPresent(): void
    {
        $existing = bin2hex(random_bytes(32));
        file_put_contents($this->envPath, "encryption.key='abc'\n");
        // Simulate .env already has the key; ensure the function prefers the file value.
        file_put_contents($this->envPath, "encryption.key='abc'\nthrottle.key='$existing'\n");

        $key = provisionThrottleKey();

        $this->assertSame($existing, $key);
        $contents = file_get_contents($this->envPath);
        $this->assertStringContainsString("throttle.key='$existing'", $contents);
        // Should still be a single line
        $this->assertSame(substr_count($contents, "throttle.key="), 1);
    }

    // -- abortEncryptionConversion() / removeBackup() --

    public function testAbortEncryptionConversionRestoresFromBackup(): void
    {
        if (!is_dir(dirname($this->backupPath))) {
            mkdir(dirname($this->backupPath), 0750, true);
        }

        file_put_contents($this->envPath, "encryption.key='new'\n");
        file_put_contents($this->backupPath, "encryption.key='old'\n");

        abortEncryptionConversion();

        $this->assertSame("encryption.key='old'\n", file_get_contents($this->envPath));
    }

    public function testRemoveBackupDeletesBackupFile(): void
    {
        if (!is_dir(dirname($this->backupPath))) {
            mkdir(dirname($this->backupPath), 0750, true);
        }

        file_put_contents($this->backupPath, "encryption.key='old'\n");

        removeBackup();

        $this->assertFileDoesNotExist($this->backupPath);
    }
}

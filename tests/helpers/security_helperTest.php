<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * ROOTPATH/WRITEPATH are hard-defined constants that can't be redirected in
 * tests, so the filesystem-touching tests below operate on the project's
 * real .env and writable/backup/.env.bak. setUp()/tearDown() capture and
 * restore both files (and config('Encryption')->key) around every test —
 * do not remove those safeguards.
 */
class security_helperTest extends CIUnitTestCase
{
    private string $envPath;
    private string $backupPath;
    private string $lockPath;
    private ?string $envContentsBefore;
    private ?string $backupContentsBefore;
    private string $encryptionKeyBefore;

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

        parent::tearDown();
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

    // -- checkEncryption() — covers the persist-before-config-mutation ordering fix --

    public function testCheckEncryptionRotatesAndPersistsKeyOnSuccess(): void
    {
        file_put_contents($this->envPath, "encryption.key='tooshort'\n");
        config('Encryption')->key = 'tooshort';

        $result = checkEncryption();

        $this->assertTrue($result);
        $newKey = config('Encryption')->key;
        $this->assertNotSame('tooshort', $newKey);
        $this->assertGreaterThanOrEqual(64, strlen($newKey));
        $this->assertStringContainsString("encryption.key='$newKey'", file_get_contents($this->envPath));
    }

    public function testCheckEncryptionLeavesOldKeyOnDiskAsCommentedBackupLine(): void
    {
        file_put_contents($this->envPath, "encryption.key='tooshort'\n");
        config('Encryption')->key = 'tooshort';

        checkEncryption();

        $this->assertStringContainsString("# encryption.key='tooshort'", file_get_contents($this->envPath));
    }

    public function testCheckEncryptionDoesNotMutateConfigWhenPersistenceFails(): void
    {
        config('Encryption')->key = 'tooshort';

        // chmod() is unreliable for forcing a write failure on Windows, so
        // instead replace .env with a directory of the same name: file_exists()
        // still passes but file_get_contents()/rename() inside writeEnvKey()
        // fail, forcing checkEncryption() down its failure path.
        @unlink($this->envPath);
        mkdir($this->envPath);

        try {
            $result = checkEncryption();
        } finally {
            @rmdir($this->envPath);
        }

        $this->assertFalse($result);
        $this->assertSame('tooshort', config('Encryption')->key);
    }

    public function testCheckEncryptionSkipsRotationWhenKeyAlreadyValid(): void
    {
        $validKey = bin2hex(random_bytes(32));
        config('Encryption')->key = $validKey;
        file_put_contents($this->envPath, "encryption.key='$validKey'\n");

        $result = checkEncryption();

        $this->assertTrue($result);
        $this->assertSame($validKey, config('Encryption')->key);
    }

    // -- checkThrottleEncryption() --

    public function testCheckThrottleEncryptionProvisionsAndPersistsKey(): void
    {
        putenv('throttle.key');
        unset($_ENV['throttle.key'], $_SERVER['throttle.key']);
        file_put_contents($this->envPath, "encryption.key='abc'\n");

        $key = checkThrottleEncryption();

        $this->assertNotSame('', $key);
        $this->assertStringContainsString("throttle.key='$key'", file_get_contents($this->envPath));

        putenv('throttle.key');
        unset($_ENV['throttle.key'], $_SERVER['throttle.key']);
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

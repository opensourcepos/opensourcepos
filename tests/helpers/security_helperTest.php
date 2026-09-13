<?php

use App\Libraries\CI3SecretConverter;
use App\Models\Appconfig;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Encryption as EncryptionConfig;
use Config\Services;

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

    /**
     * Fakes the ospos_app_config rows so CI3SecretConverter can decrypt/verify
     * without a real database, and captures the payload passed to saveAll().
     *
     * @param array<string,string> $values column => CI3 ciphertext
     * @return Appconfig with a public $saved prop recording saveAll()
     */
    private function fakeAppconfig(array $values): Appconfig
    {
        $fake = new class($values) extends Appconfig {
            /** @var array<string,string>|null last payload passed to saveAll() */
            public ?array $saved = null;

            public function __construct(
                private array $vals
            ) {
                parent::__construct();
            }

            public function get_value(string $key, string $default = ''): string
            {
                return $this->vals[$key] ?? $default;
            }

            public function batch_save(array $data): bool
            {
                $this->saved = $data;

                return true;
            }
        };

        return $fake;
    }

    /**
     * Encodes $plaintext with the CI3-era cipher under $ci3Key, producing
     * the same ciphertext the real converter decrypts.
     */
    private function ci3Encrypt(string $plaintext, string $ci3Key): string
    {
        $cfg                 = new EncryptionConfig();
        $cfg->driver         = 'OpenSSL';
        $cfg->digest         = 'SHA512';
        $cfg->key            = $ci3Key;
        $cfg->cipher         = 'AES-128-CBC';
        $cfg->rawData        = false;
        $cfg->encryptKeyInfo = 'encryption';
        $cfg->authKeyInfo    = 'authentication';
        $cfg->previousKeys   = [];

        return Services::encrypter($cfg)->encrypt($plaintext);
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

    // -- envFileIsWritable() --

    public function testEnvFileIsWritableReturnsTrueWhenFileIsWritable(): void
    {
        file_put_contents($this->envPath, "encryption.key='abc'\n");
        chmod($this->envPath, 0644);

        $this->assertTrue(envFileIsWritable());
    }

    public function testEnvFileIsWritableReturnsFalseWhenFileIsReadonly(): void
    {
        file_put_contents($this->envPath, "# tmp\n");
        chmod($this->envPath, 0444);

        $this->assertFalse(envFileIsWritable());

        chmod($this->envPath, 0644);
    }

    // -- checkEncryption() --

    public function testCheckEncryptionPassesWhenKeyValid(): void
    {
        $validKey = bin2hex(random_bytes(32));
        config('Encryption')->key = $validKey;
        file_put_contents($this->envPath, "encryption.key='$validKey'\n");

        $result = checkEncryption();

        $this->assertTrue($result);
        $this->assertSame($validKey, config('Encryption')->key);
    }

    public function testCheckEncryptionProvisionsWhenKeyEmptyAndEnvWritable(): void
    {
        config('Encryption')->key = '';
        file_put_contents($this->envPath, "encryption.key=''\n");

        $result = checkEncryption();

        $this->assertTrue($result);
        $newKey = (string) config('Encryption')->key;
        $this->assertGreaterThanOrEqual(64, strlen($newKey), 'checkEncryption should provision a valid key');
        $this->assertStringContainsString("encryption.key='$newKey'", file_get_contents($this->envPath));
    }

    public function testCheckEncryptionThrowsWhenKeyEmptyAndEnvNotWritable(): void
    {
        config('Encryption')->key = '';
        file_put_contents($this->envPath, "encryption.key=''\n");
        chmod($this->envPath, 0444);

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('provisioned');
            checkEncryption();
        } finally {
            chmod($this->envPath, 0644);
        }
    }

    public function testCheckEncryptionConvertsCi3ShortKeyWhenEnvWritable(): void
    {
        // Seed CI3-era ciphertexts under a short CI3 key so the short-key
        // branch runs. A fake Appconfig model (injected via CI3SecretConverter)
        // stands in for the DB so no real database is required.
        $oldKey     = bin2hex(random_bytes(16)); // < 64 chars -> CI3 era
        $plaintext  = ['smtp_pass' => 's3cr3t-smtp', 'mailchimp_api_key' => 'mc-abc-123'];
        $ciphertext = array_map(fn ($v) => $this->ci3Encrypt($v, $oldKey), $plaintext);

        $fake = $this->fakeAppconfig($ciphertext);
        $conv = new CI3SecretConverter($fake);

        config('Encryption')->key = $oldKey;
        file_put_contents($this->envPath, "encryption.key='$oldKey'\n");

        $result = checkEncryption($conv);

        $this->assertTrue($result);

        // A fresh CI4 key was generated and is now the *active* encryption.key
        // (the old short key remains only as a commented backup line).
        $newKey = (string) config('Encryption')->key;
        $this->assertNotSame($oldKey, $newKey);
        $this->assertGreaterThanOrEqual(64, strlen($newKey));

        $contents = file_get_contents($this->envPath);
        preg_match("/^encryption\.key\s*=\s*'(.*?)'/m", $contents, $m);
        $this->assertSame($newKey, $m[1] ?? '', 'the active .env encryption.key must be the new CI4 key');

        // The saved payloads are CI4 ciphertext (not plain) and verify back.
        $this->assertNotEmpty($fake->saved);
        $this->assertSame($plaintext['smtp_pass'],         $conv->verifyAll($fake->saved)['smtp_pass'],         'smtp_pass round-trip');
        $this->assertSame($plaintext['mailchimp_api_key'], $conv->verifyAll($fake->saved)['mailchimp_api_key'], 'mailchimp_api_key round-trip');
        $this->assertNotSame($plaintext['smtp_pass'],         $fake->saved['smtp_pass'],         'saveAll() must receive ciphertext, not plain');
        $this->assertNotSame($plaintext['mailchimp_api_key'], $fake->saved['mailchimp_api_key'], 'saveAll() must receive ciphertext, not plain');
    }

    // -- checkThrottleEncryption() --

    public function testCheckThrottleEncryptionReturnsWhenKeyPresent(): void
    {
        $key = bin2hex(random_bytes(32));
        putenv("throttle.key=$key");
        $_ENV['throttle.key']   = $key;
        $_SERVER['throttle.key'] = $key;

        $result = checkThrottleEncryption();

        $this->assertSame($key, $result);
    }

    public function testCheckThrottleEncryptionProvisionsWhenKeyMissingAndEnvWritable(): void
    {
        putenv('throttle.key');
        unset($_ENV['throttle.key'], $_SERVER['throttle.key']);
        file_put_contents($this->envPath, "encryption.key='abc'\n");

        $result = checkThrottleEncryption();

        $this->assertNotSame('', $result);
        $this->assertStringContainsString("throttle.key='$result'", file_get_contents($this->envPath));
        $this->assertSame($result, $_ENV['throttle.key']);
    }

    public function testCheckThrottleEncryptionThrowsWhenKeyMissingAndEnvNotWritable(): void
    {
        putenv('throttle.key');
        unset($_ENV['throttle.key'], $_SERVER['throttle.key']);
        file_put_contents($this->envPath, "encryption.key='abc'\n");
        chmod($this->envPath, 0444);

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('provisioned');
            checkThrottleEncryption();
        } finally {
            chmod($this->envPath, 0644);
        }
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

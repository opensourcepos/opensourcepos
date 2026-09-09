<?php

namespace Tests\Libraries;

use App\Libraries\CI3SecretConverter;
use App\Models\Appconfig;
use CodeIgniter\Encryption\EncrypterInterface;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Encryption as EncryptionConfig;
use Config\Services;

/**
 * Tests for CI3SecretConverter.
 *
 * The converter performs no DB I/O of its own except saveAll(); the Appconfig
 * model is injectable so we can fake get_value()/batch_save() with an anonymous
 * subclass. decryptAll() is the only path that touches the real cipher, so we
 * verify it by encrypting a known plaintext with the *CI3 cipher* and asserting
 * the converter decrypts it back.
 */
final class CI3SecretConverterTest extends CIUnitTestCase
{
    private string $oldKey;
    private array  $plain;

    protected function setUp(): void
    {
        parent::setUp();

        // Make sure the app Encryption config has a valid key so CI4 default
        // cipher (encryptAll/verifyAll) works in this test process.
        $env = config('Encryption');
        if (empty($env->key) || strlen((string) $env->key) < 64) {
            $env->key = bin2hex(random_bytes(32));
        }

        // The CI3-era key this test uses to seed fake legacy ciphertext.
        $this->oldKey = bin2hex(random_bytes(16));

        $this->plain = [
            'clcdesq_api_key'   => 'capi-1234567890abcdef',
            'clcdesq_api_url'   => 'https://ci3.example.org/api',
            'mailchimp_api_key' => 'mc-abc-123',
            'mailchimp_list_id' => 'list-5a6b7c',
            'smtp_pass'         => 's3cr3t-smtp',
        ];
    }

    /**
     * Build the CI3 ciphertext the same way the converter decrypts it, so we
     * have a known round-trip fixture without depending on a DB row.
     */
    private function ci3Encrypt(string $plaintext): string
    {
        $cfg                = new EncryptionConfig();
        $cfg->driver        = 'OpenSSL';
        $cfg->digest        = 'SHA512';
        $cfg->key           = $this->oldKey;
        $cfg->cipher        = 'AES-128-CBC';
        $cfg->rawData       = false;
        $cfg->encryptKeyInfo = 'encryption';
        $cfg->authKeyInfo   = 'authentication';
        $cfg->previousKeys  = [];

        return Services::encrypter($cfg)->encrypt($plaintext);
    }

    /**
     * @return array<string,string>
     */
    private function ci3Ciphertexts(): array
    {
        return array_map(
            fn ($v) => $v === '' ? '' : $this->ci3Encrypt($v),
            $this->plain
        );
    }

    private function fake(array $values, bool $saveSucceeds = true): Appconfig
    {
        return new class($values, $saveSucceeds) extends Appconfig {
            public function __construct(
                private array $vals,
                private bool $ok
            ) {
                parent::__construct();
            }

            public function get_value(string $key, string $default = ''): string
            {
                return $this->vals[$key] ?? $default;
            }

            public function batch_save(array $data): bool
            {
                return $this->ok;
            }
        };
    }

    public function testDecryptAllRoundTripWithCi3Cipher(): void
    {
        $conv  = new CI3SecretConverter($this->fake($this->ci3Ciphertexts()));
        $plain = $conv->decryptAll($this->oldKey);

        foreach ($this->plain as $k => $v) {
            $this->assertSame($v, $plain[$k], "decryptAll mismatch for {$k}");
        }
    }

    public function testDecryptAllEmptyRowsStayEmpty(): void
    {
        $ct = $this->ci3Ciphertexts();
        $ct['mailchimp_api_key'] = '';
        $ct['mailchimp_list_id'] = '';

        $plain = (new CI3SecretConverter($this->fake($ct)))->decryptAll($this->oldKey);

        $this->assertSame('capi-1234567890abcdef', $plain['clcdesq_api_key']);
        $this->assertSame('',                       $plain['mailchimp_api_key']);
        $this->assertSame('',                       $plain['mailchimp_list_id']);
    }

    public function testEncryptVerifyRoundTripWithCi4Cipher(): void
    {
        $conv = new CI3SecretConverter($this->fake([]));

        $enc = $conv->encryptAll($this->plain);
        foreach ($this->plain as $k => $v) {
            $this->assertNotSame($v, $enc[$k], "encryptAll must change {$k}");
        }

        $this->assertSame($this->plain, $conv->verifyAll($enc));
    }

    public function testEncryptAllKeepsEmptyValuesEmpty(): void
    {
        $conv = new CI3SecretConverter($this->fake([]));
        $in   = $this->plain;
        $in['smtp_pass'] = '';

        $enc = $conv->encryptAll($in);
        $this->assertSame('', $enc['smtp_pass']);
        $this->assertNotSame('', $enc['clcdesq_api_key']);
    }

    public function testHasLegacyDataTrueWhenAnyPresent(): void
    {
        $conv = new CI3SecretConverter($this->fake($this->ci3Ciphertexts()));
        $this->assertTrue($conv->hasLegacyData($this->oldKey));
    }

    public function testHasLegacyDataFalseWhenAllEmpty(): void
    {
        $conv = new CI3SecretConverter($this->fake([]));
        $this->assertFalse($conv->hasLegacyData($this->oldKey));
    }

    public function testSaveAllPersistsAndReturnsTrue(): void
    {
        $conv = new CI3SecretConverter($this->fake([], true));
        $this->assertTrue($conv->saveAll(['x' => 'y']));
    }

    public function testSaveAllThrowsWhenModelFails(): void
    {
        $conv = new CI3SecretConverter($this->fake([], false));
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to save converted encryption data');
        $conv->saveAll(['x' => 'y']);
    }

    public function testLegacyKeysConstantExposesExpectedSet(): void
    {
        $expected = [
            'clcdesq_api_key',
            'clcdesq_api_url',
            'mailchimp_api_key',
            'mailchimp_list_id',
            'smtp_pass',
        ];
        $this->assertSame($expected, CI3SecretConverter::LEGACY_KEYS);
    }
}

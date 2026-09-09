<?php

namespace App\Libraries;

use App\Models\Appconfig;
use Config\Encryption as EncryptionConfig;
use Config\Services;
use RuntimeException;

/**
 * Shared converter for the CI3 -> CI4 encrypted-secret migration.
 *
 * CI3 encrypted its config secrets (SMTP password, Mailchimp API key, etc.)
 * with AES-128-CBC + `encryptKeyInfo='encryption'` + `rawData=false` +
 * `authKeyInfo='authentication'`. CI4 defaults to AES-256-CTR, and the CI4
 * "previous keys" fallback cannot bridge a *cipher* change -- it only rotates
 * keys within a single cipher. So legacy rows must be decrypted with the old
 * cipher and re-encrypted with the new one.
 *
 * This class centralises both ciphers so the interactive UI migration
 * (ConvertToCI4) and the docker startup command (env:provision) share a single
 * implementation. It performs no persistence of its own except saveAll(); key
 * rotation is left to the caller (rotateEncryptionKey in security_helper).
 */
class CI3SecretConverter
{
    /**
     * Legacy CI3 secret keys stored in the ospos_app_config table.
     *
     * @var list<string>
     */
    public const LEGACY_KEYS = [
        'clcdesq_api_key',
        'clcdesq_api_url',
        'mailchimp_api_key',
        'mailchimp_list_id',
        'smtp_pass',
    ];

    /**
     * @var Appconfig|null
     */
    private ?Appconfig $model;

    public function __construct(?Appconfig $model = null)
    {
        $this->model = $model;
    }

    /**
     * Decrypts all legacy CI3 secrets into plaintext using the CI3 cipher and
     * the supplied key. Empty/missing rows become ''.
     *
     * @param string $key the CI3-era encryption key
     * @return array<string,string>
     */
    public function decryptAll(string $key): array
    {
        $encrypter = Services::encrypter($this->ci3Config($key));
        $appConfig = $this->resolveModel();

        $result = [];
        foreach (self::LEGACY_KEYS as $col) {
            $value = (string) $appConfig->get_value($col);
            $result[$col] = $value === '' ? '' : $encrypter->decrypt($value);
        }

        return $result;
    }

    /**
     * Encrypts plaintext values under the current CI4 cipher (no persistence).
     *
     * @param array<string,string> $plain
     * @return array<string,string>
     */
    public function encryptAll(array $plain): array
    {
        $encrypter = Services::encrypter();

        $result = [];
        foreach ($plain as $col => $value) {
            $value = (string) $value;
            $result[$col] = $value === '' ? '' : $encrypter->encrypt($value);
        }

        return $result;
    }

    /**
     * Decrypts CI4-cipher values back to plaintext. Used to verify a round
     * trip before persisting the converted result.
     *
     * @param array<string,string> $encrypted
     * @return array<string,string>
     */
    public function verifyAll(array $encrypted): array
    {
        $encrypter = Services::encrypter();

        $result = [];
        foreach ($encrypted as $col => $value) {
            $value = (string) $value;
            $result[$col] = $value === '' ? '' : $encrypter->decrypt($value);
        }

        return $result;
    }

    /**
     * Persists already-encrypted values to the ospos_app_config table.
     *
     * @param array<string,string> $encrypted
     * @return bool
     * @throws RuntimeException
     */
    public function saveAll(array $encrypted): bool
    {
        if (!$this->resolveModel()->batch_save($encrypted)) {
            throw new RuntimeException('Failed to save converted encryption data. Check logs for details.');
        }

        return true;
    }

    /**
     * True when at least one legacy secret decrypts to a non-empty plaintext
     * value with $oldKey. Used to decide whether a conversion is necessary.
     */
    public function hasLegacyData(string $oldKey): bool
    {
        foreach ($this->decryptAll($oldKey) as $value) {
            if ((string) $value !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * Build a Config\Encryption instance matching the CI3 cipher settings so
     * Services::encrypter() can construct a handler with AES-128-CBC.
     *
     * @return EncryptionConfig
     */
    private function ci3Config(string $key): EncryptionConfig
    {
        $config        = new EncryptionConfig();
        $config->driver = 'OpenSSL';
        $config->digest = 'SHA512';
        $config->key    = $key;
        $config->cipher = 'AES-128-CBC';
        $config->rawData = false;
        $config->encryptKeyInfo = 'encryption';
        $config->authKeyInfo = 'authentication';
        $config->previousKeys = [];

        return $config;
    }

    /**
     * @return Appconfig
     */
    private function resolveModel(): Appconfig
    {
        if ($this->model === null) {
            $this->model = model(Appconfig::class);
        }

        return $this->model;
    }
}

<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Encryption configuration.
 *
 * These are the settings used for encryption, if you don't pass a parameter
 * array to the encrypter for creation/initialization.
 */
class Encryption extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Encryption Key Starter
     * --------------------------------------------------------------------------
     *
     * If you use the Encryption class you must set an encryption key (seed).
     * You need to ensure it is long enough for the cipher and mode you plan to use.
     * See the user guide for more info.
     */
    public string $key = '';

    /**
     * --------------------------------------------------------------------------
     * Previous Encryption Keys
     * --------------------------------------------------------------------------
     *
     * When rotating encryption keys, add old keys here to maintain ability
     * to decrypt data encrypted with previous keys. Encryption always uses
     * the current $key. Decryption tries current key first, then falls back
     * to previous keys if decryption fails.
     *
     * In .env file, use comma-separated string:
     *   encryption.previousKeys = hex2bin:9be8c64fcea509867...,hex2bin:3f5a1d8e9c2b7a4f6...
     *
     * @var list<string>|string
     */
    public array|string $previousKeys = '';

    /**
     * --------------------------------------------------------------------------
     * Encryption Driver to Use
     * --------------------------------------------------------------------------
     *
     * One of the supported encryption drivers.
     *
     * Available drivers:
     * - OpenSSL
     * - Sodium
     */
    public string $driver = 'OpenSSL';

    /**
     * --------------------------------------------------------------------------
     * SodiumHandler's Padding Length in Bytes
     * --------------------------------------------------------------------------
     *
     * This is the number of bytes that will be padded to the plaintext message
     * before it is encrypted. This value should be greater than zero.
     *
     * See the user guide for more information on padding.
     */
    public int $blockSize = 16;

    /**
     * --------------------------------------------------------------------------
     * Encryption digest
     * --------------------------------------------------------------------------
     *
     * HMAC digest to use, e.g. 'SHA512' or 'SHA256'. Default value is 'SHA512'.
     */
    public string $digest = 'SHA512';

    /**
     * Whether the cipher-text should be raw. If set to false, then it will be base64 encoded.
     * This setting is only used by OpenSSLHandler.
     *
     * Set to false for CI3 Encryption compatibility.
     */
    public bool $rawData = false;

    /**
     * Encryption key info.
     * This setting is only used by OpenSSLHandler.
     *
     * Set to 'encryption' for CI3 Encryption compatibility.
     */
    public string $encryptKeyInfo = '';

    /**
     * Authentication key info.
     * This setting is only used by OpenSSLHandler.
     *
     * Set to 'authentication' for CI3 Encryption compatibility.
     */
    public string $authKeyInfo = '';

    /**
     * Cipher to use.
     * This setting is only used by OpenSSLHandler.
     *
     * Set to 'AES-128-CBC' to decrypt encrypted data that encrypted
     * by CI3 Encryption default configuration.
     */
    public string $cipher = 'AES-256-CTR';

    public function __construct()
    {
        parent::__construct();

        if ($this->key === '') {
            // Fallback sources (notably the ENCRYPTION_KEY Docker var, which the
            // parent never reads) were not decode-parsed, so run them through
            // the same parser to keep hex2bin:/base64: keys consistent.
            $this->key = self::parseKey(self::resolveKey(
                (string) ($_SERVER['encryption.key'] ?? ''),
                (string) ($_ENV['encryption.key'] ?? ''),
                (string) getenv('encryption.key'),
                (string) getenv('ENCRYPTION_KEY'),
            ));
        }
    }

    /**
     * Decode a key's `hex2bin:`/`base64:` prefix, mirroring
     * BaseConfig::parseEncryptionKey(); kept static so it is unit-testable.
     */
    public static function parseKey(string $key): string
    {
        if (str_starts_with($key, 'hex2bin:')) {
            return (string) hex2bin(substr($key, 8));
        }

        if (str_starts_with($key, 'base64:')) {
            return (string) base64_decode(substr($key, 7), true);
        }

        return $key;
    }

    /**
     * Return the first non-empty source (highest precedence first). Cascading
     * past empty strings (vs `??`) avoids a blank value shadowing the real key.
     */
    public static function resolveKey(string ...$sources): string
    {
        foreach ($sources as $source) {
            if ($source !== '') {
                return $source;
            }
        }

        return '';
    }
}

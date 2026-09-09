<?php

namespace App\Commands;

use App\Libraries\CI3SecretConverter;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Exception;

/**
 * Idempotent startup provisioning of the encryption + throttle keys.
 *
 * The web runtime is strictly read-only with respect to these secrets (see
 * checkEncryption()/checkThrottleEncryption() guards). Keys are therefore
 * minted here at container start via `php spark env:provision`, and for
 * legacy CI3 deployments this command re-encrypts stored CI3 secrets to the
 * CI4 cipher (shared implementation with the interactive ConvertToCI4
 * migration).
 */
class EnvProvision extends BaseCommand
{
    /**
     * The command's group.
     *
     * @var string
     */
    protected $group = 'Environment';

    /**
     * The command's name.
     *
     * @var string
     */
    protected $name = 'env:provision';

    /**
     * The command's usage.
     *
     * @var string
     */
    protected $usage = 'env:provision';

    /**
     * The command's short description.
     *
     * @var string
     */
    protected $description = 'Ensures the encryption and throttle keys are provisioned and converts any legacy CI3-encrypted secrets.';

    /**
     * Execute the command.
     *
     * @param array<int|string, string|null> $params
     */
    public function run(array $params): void
    {
        helper('security');

        // 1. Throttle key — always idempotent, independent of the encryption key.
        $throttleKey = provisionThrottleKey();
        CLI::write('throttle.key       : ' . ($throttleKey !== '' ? 'present' : 'MISSING'), 'green');

        // 2. Encryption key + optional CI3 -> CI4 conversion.
        $encryptionConfig = config('Encryption');
        $key = (string) ($encryptionConfig->key ?? '');

        if ($key !== '' && strlen($key) >= 64) {
            CLI::write('encryption.key     : CI4 key already present', 'green');
            CLI::newLine();

            return;
        }

        $converter = new CI3SecretConverter();

        if ($key !== '' && strlen($key) < 64) {
            // Legacy CI3 key is present in .env: decrypt stored secrets with it,
            // rotate to a strong CI4 key, then re-encrypt under the new key.
            $plain = $converter->decryptAll($key);
            $hasData = $this->anyNonEmpty($plain);

            rotateEncryptionKey($key);
            CLI::write('encryption.key     : rotated CI3 -> CI4 key', 'green');

            if ($hasData) {
                $converter->saveAll($plain);
                CLI::write('legacy secrets     : converted to CI4 cipher', 'green');
            }
        } else {
            // No key at all. Generate one. We cannot recover existing CI3
            // ciphertext (no CI3 key), so warn if any secret rows are present.
            rotateEncryptionKey(null);
            CLI::write('encryption.key     : new CI4 key generated', 'green');

            if ($this->legacySecretsPresent()) {
                CLI::write('legacy secrets     : WARNING - stored CI3 secrets found but no CI3 key to decrypt them; they could not be recovered', 'yellow');
            }
        }

        CLI::newLine();
        CLI::write('env:provision complete.', 'green');
        CLI::newLine();
    }

    /**
     * @param array<string, string> $plain
     */
    private function anyNonEmpty(array $plain): bool
    {
        foreach ($plain as $value) {
            if ((string) $value !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * True when any legacy secret row holds a non-empty value (encrypted or not).
     * Used only to warn about unrecoverable CI3 data when no CI3 key is present.
     */
    private function legacySecretsPresent(): bool
    {
        try {
            $appConfig = model('Appconfig');
        } catch (Exception $e) {
            return false;
        }

        foreach (CI3SecretConverter::LEGACY_KEYS as $col) {
            try {
                if ($appConfig->get_value($col) !== '') {
                    return true;
                }
            } catch (Exception $e) {
                return false;
            }
        }

        return false;
    }
}

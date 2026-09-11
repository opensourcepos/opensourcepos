<?php

namespace App\Commands;

use App\Libraries\CI3SecretConverter;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Exception;
use RuntimeException;

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

    protected $name = 'env:provision';

    protected $usage = 'env:provision';

    protected $description = 'Ensures the encryption and throttle keys are provisioned and converts any legacy CI3-encrypted secrets.';

    public function run(array $params): void
    {
        helper('security');

        $throttleKey = provisionThrottleKey();
        CLI::write('throttle.key       : ' . ($throttleKey !== '' ? 'present' : 'MISSING'), 'green');

        $encryptionConfig = config('Encryption');
        $key = (string) ($encryptionConfig->key ?? '');

        if ($key !== '' && strlen($key) >= 64) {
            CLI::write('encryption.key     : CI4 key already present', 'green');
            CLI::newLine();

            return;
        }

        $converter = new CI3SecretConverter();

        if ($key !== '' && strlen($key) < 64) {
            $plain = $converter->decryptAll($key);
            $hasData = $this->anyNonEmpty($plain);

            rotateEncryptionKey($key);
            CLI::write('encryption.key     : rotated CI3 -> CI4 key', 'green');

            $encrypted = $converter->encryptAll($plain);

            if ($hasData && array_diff_assoc($plain, $converter->verifyAll($encrypted)) !== []) {
                abortEncryptionConversion();
                throw new RuntimeException('Failed to verify converted encryption data.');
            }

            if ($hasData) {
                $converter->saveAll($encrypted);
                CLI::write('legacy secrets     : converted and verified to CI4 cipher', 'green');
            }
        } else {
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

    private function anyNonEmpty(array $plain): bool
    {
        foreach ($plain as $value) {
            if ((string) $value !== '') {
                return true;
            }
        }

        return false;
    }

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

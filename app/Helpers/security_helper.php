<?php

use CodeIgniter\Encryption\Encryption;
use Config\Services;
use Random\RandomException;

/**
 * Replaces or inserts a single `key='value'` line in .env
 *
 * @param string $envKey
 * @param string $value
 * @return void
 */
function writeEnvKey(string $envKey, string $value): void
{
    $configPath = ROOTPATH . '.env';

    if (!file_exists($configPath)) {
        $examplePath = ROOTPATH . '.env.example';
        if (file_exists($examplePath)) {
            @copy($examplePath, $configPath);
        } else {
            @file_put_contents($configPath, "# OSPOS Configuration\n\n");
        }
        @chmod($configPath, 0640);
    }

    if (!file_exists($configPath)) {
        return;
    }

    $configFile = file_get_contents($configPath);
    $pattern = '/^\s*' . preg_quote($envKey, '/') . '\s*=.*/m';

    if (preg_match($pattern, $configFile)) {
        $configFile = preg_replace($pattern, "$envKey='$value'", $configFile, 1);
    } elseif (preg_match('/^encryption\.key\s*=.*$/m', $configFile, $matches, PREG_OFFSET_CAPTURE)) {
        $insertAt = $matches[0][1] + strlen($matches[0][0]);
        $configFile = substr_replace($configFile, "\n$envKey='$value'", $insertAt, 0);
    } else {
        $configFile .= "\n$envKey='$value'\n";
    }

    @file_put_contents($configPath, $configFile);
    @chmod($configPath, 0640);
}

/**
 * @return bool
 */
function checkEncryption(): bool
{
    $oldKey = config('Encryption')->key;

    if ((empty($oldKey)) || (strlen($oldKey) < 64)) {
        $encryption = new Encryption();
        $key = bin2hex($encryption->createKey());
        config('Encryption')->key = $key;

        $configPath = ROOTPATH . '.env';
        $backupPath = WRITEPATH . '/backup/.env.bak';
        $backupFolder = WRITEPATH . '/backup';

        if (!file_exists($backupFolder)) {
            @mkdir($backupFolder, 0750, true);
        }

        if (!file_exists($configPath)) {
            $examplePath = ROOTPATH . '.env.example';
            if (file_exists($examplePath)) {
                @copy($examplePath, $configPath);
            } else {
                @file_put_contents($configPath, "# OSPOS Configuration\n\n");
            }
            @chmod($configPath, 0640);
        }

        if (file_exists($configPath)) {
            @copy($configPath, $backupPath);
            @chmod($backupPath, 0640);
            @chmod($configPath, 0640);

            writeEnvKey('encryption.key', $key);

            if (!empty($oldKey)) {
                $configFile = file_get_contents($configPath);
                $oldLine = "# encryption.key='$oldKey' REMOVE IF UNNEEDED\r\n";
                if (preg_match('/^encryption\.key\s*=/m', $configFile, $matches, PREG_OFFSET_CAPTURE)) {
                    $configFile = substr_replace($configFile, $oldLine, $matches[0][1], 0);
                    @file_put_contents($configPath, $configFile);
                    @chmod($configPath, 0640);
                }
            }

            log_message('info', "Updated encryption key in $configPath");
        }
    }

    return true;
}

/**
 * Returns a persistent secret for HMAC-hashing login-throttle cache keys.
 *
 * Deliberately independent of checkEncryption()/encryption.key: the throttle
 * filter runs before the login-triggered CI3->CI4 migration, so provisioning
 * this secret must never touch or rotate the encryption key.
 *
 * @return string
 * @throws RandomException
 */
function checkThrottleEncryption(): string
{
    $key = (string) env('throttle.key', '');

    if (!empty($key)) {
        return $key;
    }

    $key = bin2hex(random_bytes(32));
    writeEnvKey('throttle.key', $key);

    putenv("throttle.key=$key");
    $_ENV['throttle.key'] = $key;
    $_SERVER['throttle.key'] = $key;

    log_message('info', 'Provisioned throttle key in ' . ROOTPATH . '.env');

    return $key;
}

/**
 * @return void
 */
function abortEncryptionConversion(): void
{
    $configPath = ROOTPATH . '.env';
    $backupPath = WRITEPATH . '/backup/.env.bak';

    if (!file_exists($backupPath)) {
        return;
    }

    @chmod($configPath, 0640);
    $configFile = file_get_contents($backupPath);
    @file_put_contents($configPath, $configFile);
    log_message('info', "Restored $configPath from backup");
}

/**
 * @return void
 */
function removeBackup(): void
{
    $backupPath = WRITEPATH . '/backup/.env.bak';
    if (!file_exists($backupPath)) {
        return;
    }
    @unlink($backupPath);
    log_message('info', "Removed $backupPath");
}

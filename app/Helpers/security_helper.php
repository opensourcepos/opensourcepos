<?php

use CodeIgniter\Encryption\Encryption;
use Config\Services;
use Random\RandomException;

/**
 * Replaces or inserts a single `key='value'` line in .env
 *
 * @param string $envKey
 * @param string $value
 * @return bool true on success, false if the write could not be completed
 */
function writeEnvKey(string $envKey, string $value): bool
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
        return false;
    }

    $handle = @fopen($configPath, 'c+');
    if ($handle === false) {
        return false;
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            return false;
        }

        $configFile = stream_get_contents($handle);
        if ($configFile === false) {
            return false;
        }

        $configFile = applyEnvKeyReplacement($configFile, $envKey, $value);

        return atomicWriteFile($configPath, $configFile);
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}

/**
 * @param string $configFile
 * @param string $envKey
 * @param string $value
 * @return string
 */
function applyEnvKeyReplacement(string $configFile, string $envKey, string $value): string
{
    $pattern = '/^\s*' . preg_quote($envKey, '/') . '\s*=.*/m';

    if (preg_match($pattern, $configFile)) {
        return preg_replace($pattern, "$envKey='$value'", $configFile, 1);
    }

    if (preg_match('/^encryption\.key\s*=.*$/m', $configFile, $matches, PREG_OFFSET_CAPTURE)) {
        $insertAt = $matches[0][1] + strlen($matches[0][0]);

        return substr_replace($configFile, "\n$envKey='$value'", $insertAt, 0);
    }

    return $configFile . "\n$envKey='$value'\n";
}

/**
 * Writes $contents to a temp file in the same directory as $path, then
 * renames it onto $path so readers never observe a partially-written file.
 *
 * @param string $path
 * @param string $contents
 * @return bool
 */
function atomicWriteFile(string $path, string $contents): bool
{
    $tmpPath = $path . '.tmp.' . uniqid();

    if (@file_put_contents($tmpPath, $contents) === false) {
        return false;
    }

    @chmod($tmpPath, 0640);

    if (!@rename($tmpPath, $path)) {
        @unlink($tmpPath);

        return false;
    }

    @chmod($path, 0640);

    return true;
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
 * @throws RuntimeException if the key cannot be durably persisted
 */
function checkThrottleEncryption(): string
{
    $key = (string) env('throttle.key', '');

    if (!empty($key)) {
        return $key;
    }

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
        throw new RuntimeException("Unable to create $configPath to provision throttle.key");
    }

    $handle = @fopen($configPath, 'c+');
    if ($handle === false) {
        throw new RuntimeException("Unable to open $configPath to provision throttle.key");
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            throw new RuntimeException("Unable to lock $configPath to provision throttle.key");
        }

        $configFile = stream_get_contents($handle);
        if ($configFile === false) {
            throw new RuntimeException("Unable to read $configPath to provision throttle.key");
        }

        // Another process may have provisioned the key while we waited for the lock.
        if (preg_match('/^\s*throttle\.key\s*=\s*[\'"]?([^\'"\r\n]*)/m', $configFile, $matches)) {
            $existing = trim($matches[1]);
            if ($existing !== '') {
                $key = $existing;
            }
        }

        if (empty($key)) {
            $key = bin2hex(random_bytes(32));
            $configFile = applyEnvKeyReplacement($configFile, 'throttle.key', $key);

            if (!atomicWriteFile($configPath, $configFile)) {
                throw new RuntimeException("Unable to persist throttle.key to $configPath");
            }
        }
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }

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

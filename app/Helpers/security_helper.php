<?php

use CodeIgniter\Encryption\Encryption;
use Config\Services;
use Random\RandomException;

/**
 * Opens (creating if needed) and exclusively locks a dedicated mutex file
 * for coordinating .env writes. Windows can't rename/delete a file while
 * any handle to it is open, so the mutex must be a separate file from
 * .env itself — never fopen()/flock() .env directly.
 *
 * @return resource
 */
function lockEnvFile()
{
    $lockPath = ROOTPATH . '.env.lock';

    $handle = @fopen($lockPath, 'c+');
    if ($handle === false) {
        $reason = error_get_last()['message'] ?? 'unknown error';
        log_message('critical', "Unable to open $lockPath: $reason");

        throw new RuntimeException("Unable to open $lockPath: $reason");
    }

    if (!flock($handle, LOCK_EX)) {
        fclose($handle);

        $reason = error_get_last()['message'] ?? 'unknown error';
        log_message('critical', "Unable to lock $lockPath: $reason");

        throw new RuntimeException("Unable to lock $lockPath: $reason");
    }

    return $handle;
}

/**
 * @param resource $handle
 * @return void
 */
function unlockEnvFile($handle): void
{
    flock($handle, LOCK_UN);
    fclose($handle);
}

/**
 * Copies .env.example to .env (or creates a stub) if .env does not exist yet.
 *
 * @param string $configPath
 * @return bool true if $configPath exists (already did, or was just created)
 */
function initializeEnvFile(string $configPath): bool
{
    if (!file_exists($configPath)) {
        $examplePath = ROOTPATH . '.env.example';
        if (file_exists($examplePath)) {
            @copy($examplePath, $configPath);
        } else {
            @file_put_contents($configPath, "# OSPOS Configuration\n\n");
        }
        @chmod($configPath, 0640);
    }

    return file_exists($configPath);
}

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

    if (!initializeEnvFile($configPath)) {
        return false;
    }

    $lock = lockEnvFile();

    try {
        $configFile = @file_get_contents($configPath);
        if ($configFile === false) {
            return false;
        }

        $updated = applyEnvKeyReplacement($configFile, $envKey, $value);
        if ($updated === null) {
            return false;
        }

        return atomicWriteFile($configPath, $updated);
    } finally {
        unlockEnvFile($lock);
    }
}

function applyEnvKeyReplacement(string $configFile, string $envKey, string $value): ?string
{
    $pattern = '/^\s*' . preg_quote($envKey, '/') . '\s*=.*/m';

    if (preg_match($pattern, $configFile)) {
        $escapedValue = str_replace(['\\', '$'], ['\\\\', '\\$'], $value);

        return preg_replace_callback($pattern, static fn () => "$envKey='$escapedValue'", $configFile, 1);
    }

    // New keys insert right after encryption.key so it stays first for easy backup/rotation.
    if (preg_match('/^encryption\.key\s*=.*$/m', $configFile, $matches, PREG_OFFSET_CAPTURE)) {
        $insertAt = (int) $matches[0][1] + strlen($matches[0][0]);

        return substr_replace($configFile, "\n$envKey='$value'", $insertAt, 0);
    }

    return $configFile . "\n$envKey='$value'\n";
}

/**
 * Writes $contents to a temp file in the same directory as $path, then
 * renames it onto $path so readers never observe a partially written file.
 *
 * @param string $path
 * @param string $contents
 * @return bool
 * @throws RandomException
 */
function atomicWriteFile(string $path, string $contents): bool
{
    $tmpPath = $path . '.tmp.' . bin2hex(random_bytes(8));

    $handle = @fopen($tmpPath, 'x');
    if ($handle === false) {
        return false;
    }

    if (!@chmod($tmpPath, 0640)) {
        fclose($handle);
        @unlink($tmpPath);

        return false;
    }

    $written = fwrite($handle, $contents);
    if ($written === false || $written !== strlen($contents) || !fflush($handle)) {
        fclose($handle);
        @unlink($tmpPath);

        return false;
    }

    if (function_exists('fsync') && !fsync($handle)) {
        fclose($handle);
        @unlink($tmpPath);

        return false;
    }

    fclose($handle);

    // On Windows rename() does not overwrite an existing destination. Fall back to unlink()+rename().
    if (!@rename($tmpPath, $path)) {
        if (PHP_OS_FAMILY !== 'Windows' || !@unlink($path) || !@rename($tmpPath, $path)) {
            @unlink($tmpPath);

            return false;
        }
    }

    @chmod($path, 0640);

    return true;
}

/**
 * Copies $configPath to $backupPath, creating the backup folder if needed.
 *
 * @param string $configPath
 * @param string $backupPath
 * @return void
 */
function backupEnvFile(string $configPath, string $backupPath): void
{
    $backupFolder = dirname($backupPath);

    if (!file_exists($backupFolder)) {
        @mkdir($backupFolder, 0750, true);
    }

    @copy($configPath, $backupPath);
    @chmod($backupPath, 0640);
    @chmod($configPath, 0640);
}

/**
 * Applies the new encryption.key to $configFile, preserving $oldKey as a
 * commented-out backup line immediately before it.
 *
 * @param string $configFile
 * @param string $key
 * @param string $oldKey
 * @return string|null updated file contents, or null if the replacement failed
 */
function writeNewEncryptionKey(string $configFile, string $key, string $oldKey): ?string
{
    $updated = applyEnvKeyReplacement($configFile, 'encryption.key', $key);
    if ($updated === null) {
        return null;
    }

    if (!empty($oldKey)) {
        $oldLine = "# encryption.key='$oldKey' REMOVE IF UNNEEDED\r\n";
        if (preg_match('/^encryption\.key\s*=/m', $updated, $matches, PREG_OFFSET_CAPTURE)) {
            $updated = substr_replace($updated, $oldLine, $matches[0][1], 0);
        }
    }

    return $updated;
}

/**
 * @return bool true on successful encryption check.
 * @throws RandomException
 */
function checkEncryption(): bool
{
    $oldKey = config('Encryption')->key;

    if ((empty($oldKey)) || (strlen($oldKey) < 64)) {
        $encryption = new Encryption();
        $key = bin2hex($encryption->createKey());

        $configPath = ROOTPATH . '.env';
        $backupPath = WRITEPATH . '/backup/.env.bak';

        if (!initializeEnvFile($configPath)) {
            return true;
        }

        backupEnvFile($configPath, $backupPath);

        $lock = lockEnvFile();

        try {
            $configFile = @file_get_contents($configPath);
            if ($configFile === false) {
                return false;
            }

            $updated = writeNewEncryptionKey($configFile, $key, $oldKey);
            if ($updated === null) {
                return false;
            }

            if (!atomicWriteFile($configPath, $updated)) {
                return false;
            }
        } finally {
            unlockEnvFile($lock);
        }

        config('Encryption')->key = $key;

        log_message('info', "Updated encryption key in $configPath");
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

    if (!initializeEnvFile($configPath)) {
        throw new RuntimeException("Unable to create $configPath to provision throttle.key");
    }

    $lock = lockEnvFile();

    try {
        $configFile = file_get_contents($configPath);
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
            $updated = applyEnvKeyReplacement($configFile, 'throttle.key', $key);

            if ($updated === null || !atomicWriteFile($configPath, $updated)) {
                throw new RuntimeException("Unable to persist throttle.key to $configPath");
            }
        }
    } finally {
        unlockEnvFile($lock);
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

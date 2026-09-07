<?php

use CodeIgniter\Encryption\Encryption;
use CodeIgniter\Encryption\Exceptions\EncryptionException;
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
        throw new RuntimeException("Unable to open $lockPath");
    }

    if (!flock($handle, LOCK_EX)) {
        fclose($handle);

        throw new RuntimeException("Unable to lock $lockPath");
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

    $lock = lockEnvFile();

    try {
        $configFile = file_get_contents($configPath);
        if ($configFile === false) {
            return false;
        }

        $configFile = applyEnvKeyReplacement($configFile, $envKey, $value);

        return atomicWriteFile($configPath, $configFile);
    } finally {
        unlockEnvFile($lock);
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

    // rename() overwrites an existing destination on POSIX. On Windows it
    // does not, so fall back to unlink()+rename() there. Callers must not
    // hold any open handle on $path — Windows can't unlink/rename a path
    // that's still open, even by the same process.
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
 * Checks and initializes encryption key.
 *
 * This function ensures a valid encryption key exists for the application.
 * It tries multiple storage locations to support different deployment scenarios:
 * 1. ROOTPATH/.env - Standard location for non-containerized deployments
 * 2. WRITEPATH/config/encryption.key - Fallback for Docker/container environments where .env is read-only
 *
 * @return bool True if encryption key is available, false if key generation/persistence failed
 */
function checkEncryption(): bool
{
    $oldKey = config('Encryption')->key;

    if (!empty($oldKey) && strlen($oldKey) >= 64) {
        return true;
    }

    $encryption = new Encryption();
    $key = bin2hex($encryption->createKey());
    config('Encryption')->key = $key;

    $envPersisted = writeEncryptionKeyToEnv($key, $oldKey);
    $writablePersisted = writeEncryptionKeyToWritable($key, $oldKey);
    $persisted = $envPersisted || $writablePersisted;

    if ($persisted) {
        log_message('info', 'Encryption key initialized successfully');
    } else {
        log_message('error', 'Failed to persist encryption key to any location. Encryption may not survive container restarts.');
    }

    return $persisted;
}

/**
 * Writes encryption key to ROOTPATH/.env file.
 *
 * @param string      $key     The new encryption key (hex-encoded)
 * @param string|null $oldKey  The previous key to preserve for key rotation
 *
 * @return bool True if key was written successfully, false otherwise
 */
function writeEncryptionKeyToEnv(string $key, ?string $oldKey = null): bool
{
    $configPath = ROOTPATH . '.env';
    $backupPath = WRITEPATH . 'backup' . DIRECTORY_SEPARATOR . '.env.bak';
    $backupFolder = WRITEPATH . 'backup';

    if (!file_exists($backupFolder)) {
        if (!@mkdir($backupFolder, 0750, true)) {
            log_message('debug', 'Could not create backup directory');
        }
    }

    if (!file_exists($configPath)) {
        $examplePath = ROOTPATH . '.env.example';
        if (file_exists($examplePath)) {
            if (!@copy($examplePath, $configPath)) {
                log_message('debug', 'Could not copy .env.example to .env');
            }
        } else {
            if (!@file_put_contents($configPath, "# OSPOS Configuration\n\n") !== false) {
                log_message('debug', 'Could not create .env file');
            }
        }
        @chmod($configPath, 0640);
    }

    if (!is_writable($configPath)) {
        log_message('debug', '.env file is not writable');
        return false;
    }

    if (file_exists($configPath)) {
        @copy($configPath, $backupPath);
        @chmod($backupPath, 0640);
    }

    $configFile = file_get_contents($configPath);
    if ($configFile === false) {
        log_message('debug', 'Could not read .env file');
        return false;
    }

    if (strpos($configFile, 'encryption.key') !== false) {
        $configFile = preg_replace("/(encryption\.key.*=.*)(['\"])([^'\"]*)\\2/", "$1'$key'", $configFile);
    } else {
        $configFile .= "\nencryption.key = '$key'\n";
    }

    if (!empty($oldKey)) {
        $oldLine = "# encryption.key = '$oldKey' REMOVE IF UNNEEDED\r\n";
        $insertionPoint = stripos($configFile, 'encryption.key');
        if ($insertionPoint !== false) {
            $configFile = substr_replace($configFile, $oldLine, $insertionPoint, 0);
        }
    }

    $result = file_put_contents($configPath, $configFile);
    if ($result === false) {
        log_message('debug', 'Could not write to .env file');
        return false;
    }

    @chmod($configPath, 0640);
    log_message('info', "Updated encryption key in $configPath");

    return true;
}

/**
 * Writes encryption key to WRITEPATH/config/encryption.key file.
 *
 * This is the fallback location for Docker/container environments where
 * the ROOTPATH/.env file may be read-only or ephemeral.
 *
 * @param string      $key     The new encryption key (hex-encoded)
 * @param string|null $oldKey  The previous key to preserve for key rotation
 *
 * @return bool True if key was written successfully, false otherwise
 */
function writeEncryptionKeyToWritable(string $key, ?string $oldKey = null): bool
{
    $keyFile = WRITEPATH . 'config' . DIRECTORY_SEPARATOR . 'encryption.key';
    $keyDir = dirname($keyFile);

    if (!is_dir($keyDir)) {
        if (!@mkdir($keyDir, 0750, true)) {
            log_message('error', 'Could not create config directory: ' . $keyDir);
            return false;
        }
    }

    if (!is_writable($keyDir)) {
        log_message('error', 'Config directory is not writable: ' . $keyDir);
        return false;
    }

    $data = [
        'key'           => $key,
        'previous_keys' => [],
        'generated_at'  => date('c'),
        'generated_by'  => 'checkEncryption()',
    ];

    if (!empty($oldKey)) {
        $data['previous_keys'][] = $oldKey;
    }

    $content = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    $result = file_put_contents($keyFile, $content);

    if ($result === false) {
        log_message('error', 'Could not write encryption key file');
        return false;
    }

    @chmod($keyFile, 0640);

    log_message('info', "Stored encryption key in $keyFile");

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
            $configFile = applyEnvKeyReplacement($configFile, 'throttle.key', $key);

            if (!atomicWriteFile($configPath, $configFile)) {
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
 * Loads encryption key from WRITEPATH/config/encryption.key file.
 *
 * This is the fallback key loader for Docker/container environments.
 *
 * @return string|null The encryption key if found, null otherwise
 */
function loadEncryptionKeyFromWritable(): ?string
{
    $keyFile = WRITEPATH . 'config' . DIRECTORY_SEPARATOR . 'encryption.key';

    if (!file_exists($keyFile)) {
        return null;
    }

    if (!is_readable($keyFile)) {
        log_message('error', 'Encryption key file exists but is not readable: ' . $keyFile);
        return null;
    }

    $content = file_get_contents($keyFile);
    if ($content === false) {
        log_message('error', 'Could not read encryption key file');
        return null;
    }

    $data = json_decode($content, true);
    if (!is_array($data) || empty($data['key'])) {
        log_message('error', 'Encryption key file has invalid format');
        return null;
    }

    log_message('info', 'Loaded encryption key from WRITEPATH config');

    return $data['key'];
}

/**
 * Restores .env from backup (used by migration rollback).
 *
 * @return void
 */
function abortEncryptionConversion(): void
{
    $configPath = ROOTPATH . '.env';
    $backupPath = WRITEPATH . 'backup' . DIRECTORY_SEPARATOR . '.env.bak';

    if (!file_exists($backupPath)) {
        return;
    }

    $configFile = file_get_contents($backupPath);
    if ($configFile === false) {
        log_message('error', 'Could not read backup file for restoration');
        return;
    }

    if (file_put_contents($configPath, $configFile) !== false) {
        @chmod($configPath, 0640);
        log_message('info', "Restored $configPath from backup");
    } else {
        log_message('error', "Failed to restore $configPath from backup");
    }
}

/**
 * Removes backup file (used after successful migration).
 *
 * @return void
 */
function removeBackup(): void
{
    $backupPath = WRITEPATH . 'backup' . DIRECTORY_SEPARATOR . '.env.bak';

    if (file_exists($backupPath)) {
        unlink($backupPath);
    }
}

/**
 * Decrypts an encrypted value with proper error handling.
 *
 * This function provides a consistent decryption pattern across the codebase,
 * handling cases where encryption key may not be available or decryption fails.
 *
 * @param string|null $encryptedValue The encrypted value to decrypt
 * @param string      $default        Default value to return if decryption fails
 *
 * @return string The decrypted value, or default if decryption fails
 */
function decryptValue(?string $encryptedValue, string $default = ''): string
{
    if ($encryptedValue === null || $encryptedValue === '') {
        return $default;
    }

    if (!checkEncryption()) {
        log_message('warning', 'Cannot decrypt value: encryption key not available');
        return $default;
    }

    try {
        $encrypter = Services::encrypter();
        return $encrypter->decrypt($encryptedValue);
    } catch (EncryptionException $e) {
        log_message('error', 'Decryption failed: ' . $e->getMessage());
        return $default;
    }
}

/**
 * Encrypts a value with proper error handling.
 *
 * This function provides a consistent encryption pattern across the codebase,
 * handling cases where encryption key may not be available.
 *
 * @param string|null $value   The value to encrypt
 * @param bool        $require Whether encryption is required (returns empty string on failure)
 *                              If false, returns original value on failure
 *
 * @return string The encrypted value, or empty string/original value if encryption fails
 */
function encryptValue(?string $value, bool $require = true): string
{
    if ($value === null || $value === '') {
        return '';
    }

    if (!checkEncryption()) {
        log_message('error', 'Cannot encrypt value: encryption key not available');
        return $require ? '' : $value;
    }

    try {
        $encrypter = Services::encrypter();
        return $encrypter->encrypt($value);
    } catch (EncryptionException $e) {
        log_message('error', 'Encryption failed: ' . $e->getMessage());
        return $require ? '' : $value;
    }
}
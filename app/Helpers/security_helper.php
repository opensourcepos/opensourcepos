<?php

use CodeIgniter\Encryption\Encryption;
use CodeIgniter\Encryption\Exceptions\EncryptionException;
use Config\Services;

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
<?php

use App\Libraries\CI3SecretConverter;
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
    $lockPath = config('SecurityEnv')->lockPath;

    $handle = @fopen($lockPath, 'c+');
    if ($handle === false) {
        $reason = error_get_last()['message'] ?? 'unknown error';
        log_message('critical', "Unable to open $lockPath: $reason");

        throw new RuntimeException(lang('Error.unable_to_open_lock_file', ['filePath' => $lockPath, 'reason' => $reason]));
    }

    if (!flock($handle, LOCK_EX)) {
        fclose($handle);

        $reason = error_get_last()['message'] ?? 'unknown error';
        log_message('critical', "Unable to lock $lockPath: $reason");

        throw new RuntimeException(lang('Error.unable_to_lock_file', ['filePath' => $lockPath, 'reason' => $reason]));
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
    $configPath = config('SecurityEnv')->envPath;

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
    $escapedValue = str_replace(['\\', '$'], ['\\\\', '\\$'], $value);

    if (preg_match($pattern, $configFile)) {
        return preg_replace_callback($pattern, static fn () => "$envKey='$escapedValue'", $configFile, 1);
    }

    // New keys insert right after encryption.key so it stays first for easy backup/rotation.
    if (preg_match('/^encryption\.key\s*=.*$/m', $configFile, $matches, PREG_OFFSET_CAPTURE)) {
        $insertAt = (int) $matches[0][1] + strlen($matches[0][0]);

        return substr_replace($configFile, "\n$envKey='$escapedValue'", $insertAt, 0);
    }

    return $configFile . "\n$envKey='$escapedValue'\n";
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
 * @return bool true when the backup exists and is readable, false otherwise
 */
function backupEnvFile(string $configPath, string $backupPath): bool
{
    $backupFolder = dirname($backupPath);

    if (!file_exists($backupFolder) && !@mkdir($backupFolder, 0750, true)) {
        return false;
    }

    if (!@copy($configPath, $backupPath)) {
        return false;
    }

    if (!is_readable($backupPath)) {
        return false;
    }

    if (@chmod($backupPath, 0640) !== true || @chmod($configPath, 0640) !== true) {
        return false;
    }

    return true;
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
 * Returns true when the current process can write to (or create) .env.
 *
 * A missing .env file is considered writable when the directory is writable.
 *
 * @return bool
 */
function envFileIsWritable(): bool
{
    $configPath = config('SecurityEnv')->envPath;

    return file_exists($configPath)
        ? is_writable($configPath)
        : is_writable(dirname($configPath));
}

/**
 * Ensures a usable CI4 encryption key is available.
 *
 * Behaviour:
 * - Key present and >= 64 characters: returns true immediately (no I/O).
 * - Key missing or CI3-era (< 64 chars) and .env IS writable:
 *   - Short key: decrypts legacy CI3 secrets, rotates to a fresh CI4 key,
 *     re-encrypts under the new key, verifies the round-trip, persists result.
 *   - Missing key: generates a fresh key and persists it.
 * - Key missing or CI3-era (< 64 chars) and .env NOT writable:
 *   throws — the key was presumably already provisioned externally
 *   (e.g. `php spark env:provision` at container startup); the in-memory
 *   config simply hasn't been reloaded yet.
 *
 * @param CI3SecretConverter|null $converter injectable converter used to
 *                                           decrypt/re-encrypt legacy CI3
 *                                           secrets in the short-key branch;
 *                                           defaults to the real converter
 *                                           (tests may pass a fake to avoid
 *                                           hitting the database)
 * @return bool true when a valid key is available
 * @throws Throwable when .env is not writable or the CI3 -> CI4 conversion fails
 */
function checkEncryption(?CI3SecretConverter $converter = null): bool
{
    $key = (string) config('Encryption')->key;

    if ($key !== '' && strlen($key) >= 64) {
        return true;
    }

    if (!envFileIsWritable()) {
        log_message('critical', 'Encryption key not provisioned and .env is not writable. Run `php spark env:provision` to generate one.');

        throw new RuntimeException(lang('Error.encryption_key_not_provisioned'));
    }

    if ($key !== '') {
        $converter = $converter ?? new CI3SecretConverter();

        // Legacy plaintext is a DB read; safe outside the .env lock (the lock
        // only guards the .env file write, which the rotation performs).
        $plain = $converter->decryptAll($key);

        $nonEmpty = false;
        foreach ($plain as $value) {
            if ((string) $value !== '') {
                $nonEmpty = true;
                break;
            }
        }

        // Run the whole backup -> rotate -> re-encrypt -> verify -> persist unit
        // under a single .env lock. On any failure, the pre-rotation backup is
        // restored (also in-lock); on success the backup is removed (in-lock).
        rotateEncryptionKeyTransaction($key, static function () use ($plain, $converter, $nonEmpty): void {
            $encrypted = $converter->encryptAll($plain);

            if (array_diff_assoc($plain, $converter->verifyAll($encrypted)) !== []) {
                throw new RuntimeException(lang('Error.unable_to_persist_encryption_key', ['filePath' => config('SecurityEnv')->envPath]));
            }

            if ($nonEmpty) {
                $converter->saveAll($encrypted);
            }
        });
    } else {
        // Fresh key (no old key to decrypt): a single atomic write suffices.
        rotateEncryptionKey(null);
    }

    return true;
}

/**
 * Returns the persistent HMAC secret used to hash login-throttle cache keys.
 *
 * Behaviour:
 * - Key already present: returned immediately (no I/O).
 * - Key missing and .env IS writable: generates a fresh key and persists it.
 * - Key missing and .env NOT writable:
 *   throws — the key was presumably already provisioned externally
 *   (e.g. `php spark env:provision` at container startup).
 *
 * @return string the throttle key
 * @throws RuntimeException if the key cannot be provisioned
 */
function checkThrottleEncryption(): string
{
    $key = (string) env('throttle.key', '');

    if ($key !== '') {
        return $key;
    }

    if (!envFileIsWritable()) {
        log_message('critical', 'Throttle key not provisioned and .env is not writable. Run `php spark env:provision` to generate one.');

        throw new RuntimeException(lang('Error.throttle_key_not_provisioned'));
    }

    return provisionThrottleKey();
}

/**
 * Generates a strong encryption key, persists it to `.env` (rotating in place
 * so callers can re-encrypt CI3 data that was sealed with the old key), and
 * applies it to the running config instance.
 *
 * Standalone key-write path: acquires and holds the `.env` mutex for the
 * duration of the write, so concurrent callers see atomic key writes. Callers
 * that also run a longer multi-step conversion (re-encrypt, verify, persist)
 * must use rotateEncryptionKeyTransaction() instead, so a single lock is held
 * for the whole backup -> rotate -> re-encrypt -> persist unit and the
 * key-write does not take a nested lock on the same mutex (which would
 * deadlock).
 *
 * @param string|null $oldKey current key to rotate from; when non-empty it is
 *                            preserved as a commented backup line for the
 *                            CI3->CI4 re-encryption pass
 * @return string the newly generated key
 * @throws RuntimeException if the key could not be generated or persisted
 * @throws RandomException
 */
function rotateEncryptionKey(?string $oldKey = null): string
{
    $lock = lockEnvFile();

    try {
        return rotateEncryptionKeyUnlock($oldKey);
    } finally {
        unlockEnvFile($lock);
    }
}

/**
 * Lock-free core of rotateEncryptionKey(): generates a key, backs up .env,
 * writes the new key (atomic), and applies it to the running config.
 *
 * Do not call directly from context that does not already hold the lock —
 * that leaves a window in which another worker can read or write .env before
 * the atomic write lands. The two callers, rotateEncryptionKey() and
 * rotateEncryptionKeyTransaction(), each acquire the lock first and pass the
 * lock-free core through.
 *
 * @param string|null $oldKey current key to rotate from
 * @return string the newly generated key
 * @throws RuntimeException if the key could not be generated or persisted
 * @throws RandomException
 */
function rotateEncryptionKeyUnlock(?string $oldKey): string
{
    $encryption = new Encryption();
    $key        = bin2hex($encryption->createKey());

    $configPath = config('SecurityEnv')->envPath;
    $backupPath = config('SecurityEnv')->backupPath;

    if (!initializeEnvFile($configPath)) {
        throw new RuntimeException(lang('Error.unable_to_create_env_file', ['filePath' => $configPath]));
    }

    if (file_exists($configPath)) {
        if (!backupEnvFile($configPath, $backupPath)) {
            log_message('critical', "Unable to back up $configPath to $backupPath before rotation; aborting.");

            throw new RuntimeException(lang('Error.unable_to_persist_encryption_key', ['filePath' => $configPath]));
        }
    }

    $configFile = @file_get_contents($configPath);
    if ($configFile === false) {
        log_message('critical', "Unable to read $configPath before rotation; aborting.");

        throw new RuntimeException(lang('Error.unable_to_read_env_file', ['filePath' => $configPath]));
    }

    $updated = writeNewEncryptionKey($configFile, $key, (string) $oldKey);
    if ($updated === null) {
        throw new RuntimeException(lang('Error.unable_to_persist_encryption_key', ['filePath' => $configPath]));
    }

    if (!atomicWriteFile($configPath, $updated)) {
        throw new RuntimeException(lang('Error.unable_to_persist_encryption_key', ['filePath' => $configPath]));
    }

    config('Encryption')->key = $key;

    log_message('info', "Rotated encryption key in $configPath");

    return $key;
}

/**
 * Runs the CI3 -> CI4 key conversion as a single transaction on the `.env`
 * mutex.
 *
 * The lock is acquired BEFORE the .env backup and released only AFTER
 * $conversion has run, so the entire unit (backup -> rotate -> re-encrypt ->
 * verify -> persist) is atomic with respect to any other worker that also
 * takes the lock. The $conversion callback must throw on failure; the caller
 * is expected to catch the exception and roll back via
 * abortEncryptionConversion().
 *
 * Calls the lock-free rotateEncryptionKeyUnlock() internally so the
 * transaction lock is not re-acquired on the same mutex (which would deadlock)
 * — the mutex is held across the backup, the key write, the re-encryption,
 * verification, and persistence.
 *
 * @param string|null $oldKey current key to rotate from
 * @param callable    $conversion callback(): void, called after the new key
 *                                has been persisted and applied to the running
 *                                config; expected to perform re-encryption,
 *                                verification, and persistence. Must throw on
 *                                failure; the pre-rotation backup is restored
 *                                before the exception propagates.
 * @return string the newly generated key
 * @throws Throwable from $conversion (the lock is released in `finally`)
 * @throws RuntimeException
 * @throws RandomException
 */
function rotateEncryptionKeyTransaction(?string $oldKey, callable $conversion): string
{
    // Hold one lock for the entire transaction (backup -> rotate -> re-encrypt
    // -> verify -> persist -> cleanup). If we cannot acquire it, we fail
    // rather than silently run without synchronisation — the invariant (no
    // interleaved key + ciphertext writes) requires it.
    $lock = lockEnvFile();

    try {
        // Lock-free inner write: the outer lock IS the transaction lock, so no
        // nested re-acquire on the same mutex (which would deadlock).
        $newKey = rotateEncryptionKeyUnlock($oldKey);

        // Multi-step conversion (re-encrypt -> verify -> persist) runs while
        // still holding the lock, so a concurrent worker cannot interleave its
        // key write between the rotation and this ciphertext save.
        $conversion();

        // Success: drop the pre-rotation backup, in-lock.
        removeBackup();

        return $newKey;
    } catch (Throwable $e) {
        // Failure (CI4 EncryptionException / ReflectionException from saveAll,
        // or a failed round-trip verify, or the rotation itself failed):
        // restore the pre-rotation .env while still holding the lock, then
        // rethrow. catch runs before finally, so the restore is atomic with
        // the held lock.
        abortEncryptionConversion();

        throw $e;
    } finally {
        unlockEnvFile($lock);
    }
}

/**
 * Ensures a persistent throttle secret exists, generating and persisting one
 * to `.env` if it is missing. Idempotent: safe to call on every startup.
 *
 * @return string the throttle key
 * @throws RuntimeException if the key could not be created or persisted
 */
function provisionThrottleKey(): string
{
    $configPath = config('SecurityEnv')->envPath;

    if (!initializeEnvFile($configPath)) {
        throw new RuntimeException(lang('Error.unable_to_create_env_file', ['filePath' => $configPath]));
    }

    $lock = lockEnvFile();

    try {
        $configFile = @file_get_contents($configPath);
        if ($configFile === false) {
            log_message('critical', "Unable to read $configPath while provisioning throttle key; aborting.");

            throw new RuntimeException(lang('Error.unable_to_read_env_file', ['filePath' => $configPath]));
        }

        $key = '';
        if (preg_match('/^\s*throttle\.key\s*=\s*[\'"]?([^\'"\r\n]*)/m', $configFile, $matches)) {
            $existing = trim($matches[1]);
            if ($existing !== '') {
                $key = $existing;
            }
        }

        if ($key === '') {
            $key = bin2hex(random_bytes(32));
        }

        $updated = applyEnvKeyReplacement($configFile, 'throttle.key', $key);
        if ($updated === null || !atomicWriteFile($configPath, $updated)) {
            throw new RuntimeException(lang('Error.unable_to_persist_throttle_key', ['filePath' => $configPath]));
        }
    } finally {
        unlockEnvFile($lock);
    }

    putenv("throttle.key=$key");
    $_ENV['throttle.key'] = $key;
    $_SERVER['throttle.key'] = $key;

    log_message('info', 'Provisioned throttle key in ' . config('SecurityEnv')->envPath);

    return $key;
}

/**
 * @return void
 */
function abortEncryptionConversion(): void
{
    $configPath = config('SecurityEnv')->envPath;
    $backupPath = config('SecurityEnv')->backupPath;

    if (!file_exists($backupPath)) {
        return;
    }

    // A backup exists, so the restore must succeed or fail loudly; a silent
    // failure would leave .env holding the new key while the DB still holds the
    // old ciphertext, making the data undecryptable after the next restart.
    if (!is_file($backupPath) || !is_readable($backupPath)) {
        throw new RuntimeException(lang('Error.unable_to_read_env_file', ['filePath' => $backupPath]));
    }

    $configFile = file_get_contents($backupPath);
    if ($configFile === false) {
        throw new RuntimeException(lang('Error.unable_to_read_env_file', ['filePath' => $backupPath]));
    }

    if (!atomicWriteFile($configPath, $configFile)) {
        throw new RuntimeException(lang('Error.unable_to_persist_encryption_key', ['filePath' => $configPath]));
    }

    log_message('info', "Restored $configPath from backup");
}

/**
 * @return void
 */
function removeBackup(): void
{
    $backupPath = config('SecurityEnv')->backupPath;
    if (!file_exists($backupPath)) {
        return;
    }
    @unlink($backupPath);
    log_message('info', "Removed $backupPath");
}

<?php

namespace App\Database\Migrations;

use App\Libraries\CI3SecretConverter;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\Forge;
use CodeIgniter\Database\Migration;
use CodeIgniter\HTTP\Exceptions\RedirectException;
use RuntimeException;

class ConvertToCI4 extends Migration
{
    /**
     * Constructor.
     */
    public function __construct(?Forge $forge = null)
    {
        parent::__construct($forge);
        helper('security');
    }

    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        helper('migration');

        if (!executeScript(APPPATH . 'Database/Migrations/sqlscripts/3.4.0_CI4Conversion.sql')) {
            throw new DatabaseException('Migration script 3.4.0_CI4Conversion.sql failed. Check logs for details.');
        }

        $existingKey = (string) config('Encryption')->key;

        try {
            if ($existingKey !== '' && strlen($existingKey) < 64) {
                $this->convertCI3EncryptedData($existingKey);
            } else {
                if ($existingKey === '') {
                    rotateEncryptionKey(null);
                } else {
                    checkEncryption();
                }
            }
        } finally {
            removeBackup();
        }
    }

    /**
     * Revert a migration step.
     */
    public function down(): void {}

    /**
     * Decrypts legacy CI3-encrypted secrets with the old key, rotates to a
     * fresh CI4 key, re-encrypts under the new key, verifies the round trip,
     * and persists the result.
     *
     * The actual decryption/encryption is delegated to CI3SecretConverter so
     * the docker startup command (env:provision) shares the same code path.
     *
     * @param string $oldKey the CI3-era key currently in .env
     * @throws RedirectException
     */
    private function convertCI3EncryptedData(string $oldKey): void
    {
        $converter = new CI3SecretConverter();

        $plain = $converter->decryptAll($oldKey);

        rotateEncryptionKey($oldKey);

        $encrypted = $converter->encryptAll($plain);

        // Verify the round trip before committing so we never lose data.
        $success = empty(array_diff_assoc($plain, $converter->verifyAll($encrypted)));
        if (!$success) {
            abortEncryptionConversion();
            throw new RedirectException('login'); // TODO: Need to figure out how to pass the error to the Login controller so that it gets displayed.
        }

        try {
            $converter->saveAll($encrypted);
        } catch (RuntimeException $e) {
            abortEncryptionConversion();

            throw $e;
        }
    }
}

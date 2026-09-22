<?php

namespace App\Database\Migrations;

use App\Libraries\CI3SecretConverter;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\Forge;
use CodeIgniter\Database\Migration;
use CodeIgniter\HTTP\Exceptions\RedirectException;

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

        if ($existingKey !== '' && strlen($existingKey) < 64) {
            // Old CI3-era key: decrypt, rotate, re-encrypt, persist — all under
            // a single .env lock (see convertCI3EncryptedData).
            $this->convertCI3EncryptedData($existingKey);
        } elseif ($existingKey === '') {
            // No key at all: provision a fresh one (single atomic write), then
            // drop the incidental pre-write backup left behind by the rotation.
            rotateEncryptionKey(null);
            removeBackup();
        } else {
            // Key already present and a valid CI4 key: confirm it is usable.
            checkEncryption();
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

        // DB read, safe outside the .env lock.
        $plain = $converter->decryptAll($oldKey);

        // Backup -> rotate -> re-encrypt -> verify -> persist under one .env
        // lock. On any failure the transaction restores the pre-rotation .env
        // (still holding the lock); on success it drops the backup.
        rotateEncryptionKeyTransaction($oldKey, static function () use ($plain, $converter): void {
            $encrypted = $converter->encryptAll($plain);

            // Verify the round trip before committing so we never lose data.
            if (array_diff_assoc($plain, $converter->verifyAll($encrypted)) !== []) {
                throw new RedirectException('login'); // TODO: Need to figure out how to pass the error to the Login controller so that it gets displayed.
            }

            $converter->saveAll($encrypted);
        });
    }
}

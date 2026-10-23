<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Resolves the on-disk locations the security_helper functions read/write for
 * the runtime-generated .env secrets (encryption.key + throttle.key).
 *
 * Centralising these paths in a config object (mirroring Config\Encryption)
 * lets the helper and the test suite agree on the same source of truth, and
 * lets tests redirect all three to a per-run sandbox by mutating the shared
 * instance — without changing any helper signature.
 *
 * The defaults are the production locations; tests override them in setUp().
 */
class SecurityEnv extends BaseConfig
{
    /**
     * Path to the .env file that holds the two runtime-generated secrets.
     */
    public string $envPath = ROOTPATH . '.env';

    /**
     * Backup location used around encryption-key rotation so a failing
     * conversion can be rolled back from the previous .env contents.
     */
    public string $backupPath = WRITEPATH . '/backup/.env.bak';

    /**
     * Dedicated mutex file for coordinating concurrent .env writes. Kept
     * separate from .env itself because on Windows a file cannot be
     * renamed/deleted while a handle to it is open.
     */
    public string $lockPath = ROOTPATH . '.env.lock';
}

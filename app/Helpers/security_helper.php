<?php

use CodeIgniter\Encryption\Encryption;
use Config\Services;

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

            $configFile = file_get_contents($configPath);

            if (preg_match('/^\s*encryption\.key\s*=/m', $configFile)) {
                $configFile = preg_replace("/^(\s*encryption\.key\s*=\s*).*/m", "\$1'$key'", $configFile, 1);
            } else {
                $configFile .= "\nencryption.key = '$key'\n";
            }

            if (!empty($oldKey)) {
                $oldLine = "# encryption.key = '$oldKey' REMOVE IF UNNEEDED\r\n";
                if (preg_match('/^encryption\.key\s*=/m', $configFile, $matches, PREG_OFFSET_CAPTURE)) {
                    $configFile = substr_replace($configFile, $oldLine, $matches[0][1], 0);
                }
            }

            @file_put_contents($configPath, $configFile);
            @chmod($configPath, 0640);

            log_message('info', "Updated encryption key in $configPath");
        }
    }

    return true;
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

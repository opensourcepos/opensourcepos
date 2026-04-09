<?php

use CodeIgniter\Encryption\Encryption;
use Config\Services;

/**
 * @return bool
 */
function check_encryption(): bool
{
    $old_key = config('Encryption')->key;

    if ((empty($old_key)) || (strlen($old_key) < 64)) {
        $encryption = new Encryption();
        $key = bin2hex($encryption->createKey());
        config('Encryption')->key = $key;

        $config_path = ROOTPATH . '.env';
        $backup_path = WRITEPATH . '/backup/.env.bak';
        $backup_folder = WRITEPATH . '/backup';

        if (!file_exists($backup_folder)) {
            @mkdir($backup_folder, 0750, true);
        }

        if (!file_exists($config_path)) {
            $example_path = ROOTPATH . '.env.example';
            if (file_exists($example_path)) {
                @copy($example_path, $config_path);
            } else {
                @file_put_contents($config_path, "# OSPOS Configuration\n\n");
            }
            @chmod($config_path, 0640);
        }

        if (file_exists($config_path)) {
            @copy($config_path, $backup_path);
            @chmod($backup_path, 0640);
            @chmod($config_path, 0640);

            $config_file = file_get_contents($config_path);

            if (strpos($config_file, 'encryption.key') !== false) {
                $config_file = preg_replace("/(encryption\.key.*=.*)('.*')/", "$1'$key'", $config_file);
            } else {
                $config_file .= "\nencryption.key = '$key'\n";
            }

            if (!empty($old_key)) {
                $old_line = "# encryption.key = '$old_key' REMOVE IF UNNEEDED\r\n";
                $insertion_point = stripos($config_file, 'encryption.key');
                if ($insertion_point !== false) {
                    $config_file = substr_replace($config_file, $old_line, $insertion_point, 0);
                }
            }

            @file_put_contents($config_path, $config_file);
            @chmod($config_path, 0640);

            log_message('info', "Updated encryption key in $config_path");
        }
    }

    return true;
}

/**
 * @return void
 */
function abort_encryption_conversion(): void
{
    $config_path = ROOTPATH . '.env';
    $backup_path = WRITEPATH . '/backup/.env.bak';

    if (!file_exists($backup_path)) {
        return;
    }

    @chmod($config_path, 0640);
    $config_file = file_get_contents($backup_path);
    @file_put_contents($config_path, $config_file);
    log_message('info', "Restored $config_path from backup");
}

/**
 * @return void
 */
function remove_backup(): void
{
    $backup_path = WRITEPATH . '/backup/.env.bak';
    if (!file_exists($backup_path)) {
        return;
    }
    @unlink($backup_path);
    log_message('info', "Removed $backup_path");
}

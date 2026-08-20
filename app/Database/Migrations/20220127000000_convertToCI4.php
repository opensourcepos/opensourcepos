<?php

namespace App\Database\Migrations;

use App\Models\Appconfig;
use CodeIgniter\Database\Forge;
use CodeIgniter\Database\Migration;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\Exceptions\RedirectException;
use Config\Encryption;
use Config\Services;
use ReflectionException;

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
        executeScript(APPPATH . 'Database/Migrations/sqlscripts/3.4.0_CI4Conversion.sql');

        $existingKey = config('Encryption')->key;

        if (!empty($existingKey) && strlen($existingKey) < 64) {
            $this->convertCI3EncryptedData();
        } else {
            checkEncryption();
        }

        removeBackup();
    }

    /**
     * Revert a migration step.
     */
    public function down(): void {}

    /**
     * @return RedirectResponse|void
     * @throws ReflectionException
     */
    private function convertCI3EncryptedData()
    {
        $appConfig = model(Appconfig::class);

        $ci3EncryptedData = [
            'clcdesq_api_key'   => '',
            'clcdesq_api_url'   => '',
            'mailchimp_api_key' => '',
            'mailchimp_list_id' => '',
            'smtp_pass'         => ''
        ];

        foreach ($ci3EncryptedData as $key => $value) {
            $ci3EncryptedData[$key] = $appConfig->get_value($key);
        }

        $decryptedData = $this->decryptCI3Data($ci3EncryptedData);

        checkEncryption();

        try {
            $ci4EncryptedData = $this->encryptData($decryptedData);

            $success = empty(array_diff_assoc($decryptedData, $this->decryptData($ci4EncryptedData)));
            if (!$success) {
                abortEncryptionConversion();
                removeBackup();
                throw new RedirectException('login');
            }

            $appConfig->batch_save($ci4EncryptedData);
        } catch (RedirectException $e) {
            return redirect()->to('login'); // TODO: Need to figure out how to pass the error to the Login controller so that it gets displayed.
        }
    }

    /**
     * Decrypts CI3 encrypted data and returns the plaintext values.
     *
     * @param array $encryptedData Data encrypted using CI3 methodology.
     * @return array Plaintext, unencrypted data.
     */
    private function decryptCI3Data(array $encryptedData): array
    {
        $config = new Encryption();
        $config->driver = 'OpenSSL';
        $config->key = config('Encryption')->key;
        $config->cipher = 'AES-128-CBC';
        $config->rawData = false;
        $config->encryptKeyInfo = 'encryption';
        $config->authKeyInfo = 'authentication';

        $encrypter = Services::encrypter($config);

        return array_map(function ($value) use ($encrypter) {
            return !empty($value) ? $encrypter->decrypt($value) : '';
        }, $encryptedData);
    }

    /**
     * Encrypts data using CI4 algorithms.
     *
     * @param array $plainData Data to be encrypted.
     * @return array Encrypted data.
     */
    private function encryptData(array $plainData): array
    {
        $encrypter = Services::encrypter();

        return array_map(function ($value) use ($encrypter) {
            return !empty($value) ? $encrypter->encrypt($value) : '';
        }, $plainData);
    }

    /**
     * Decrypts data using CI4 algorithms.
     *
     * @param array $encryptedData Data to be decrypted.
     * @return array Decrypted data.
     */
    private function decryptData(array $encryptedData): array
    {
        $encrypter = Services::encrypter();

        return array_map(function ($value) use ($encrypter) {
            return !empty($value) ? $encrypter->decrypt($value) : '';
        }, $encryptedData);
    }
}

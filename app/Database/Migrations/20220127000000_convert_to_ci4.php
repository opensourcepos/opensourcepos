<?php

namespace App\Database\Migrations;

use App\Models\Appconfig;
use CodeIgniter\Database\Forge;
use CodeIgniter\Database\Migration;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\Exceptions\RedirectException;
use Config\Encryption;
use Config\Services;

class Convert_to_ci4 extends Migration
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
		error_log('Migrating database to CodeIgniter4 formats');

		helper('migration');
		execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.4.0_ci4_conversion.sql');

		if(!empty(config('Encryption')->key))
		{
			$this->convert_ci3_encrypted_data();
		}
		else
		{
			check_encryption();
		}

		remove_backup();

		error_log('Migrating to CodeIgniter4 formats completed');
	}

	/**
	 * Revert a migration step.
	 */
	public function down(): void
	{

	}

	/**
	 * @return RedirectResponse|void
	 * @throws \ReflectionException
	 */
	private function convert_ci3_encrypted_data()
	{
		$appconfig = model(Appconfig::class);

		$ci3_encrypted_data = [
			'clcdesq_api_key' => '',
			'clcdesq_api_url' => '',
			'mailchimp_api_key' => '',
			'mailchimp_list_id' => '',
			'smtp_pass' => ''
		];

		foreach($ci3_encrypted_data as $key => $value)
		{
			$ci3_encrypted_data[$key] = $appconfig->get_value($key);
		}

		$decrypted_data = $this->decrypt_ci3_data($ci3_encrypted_data);

		check_encryption();

		try
		{
			$ci4_encrypted_data = $this->encrypt_data($decrypted_data);

			$success = empty(array_diff_assoc($decrypted_data, $this->decrypt_data($ci4_encrypted_data)));
			if(!$success)
			{
				abort_encryption_conversion();
				remove_backup();
				throw new RedirectException('login');
			}

			$appconfig->batch_save($ci4_encrypted_data);
		} catch(RedirectException $e)
		{
			return redirect()->to('login'); //TODO: Need to figure out how to pass the error to the Login controller so that it gets displayed.
		}
	}

	/**
	 * Decrypts CI3 encrypted data and returns the plaintext values.
	 *
	 * @param array $encrypted_data Data encrypted using CI3 methodology.
	 * @return array Plaintext, unencrypted data.
	 */
	private function decrypt_ci3_data(array $encrypted_data): array
	{
		$config = new Encryption();
		$config->driver = 'OpenSSL';
		$config->key = config('Encryption')->key;
		$config->cipher = 'AES-128-CBC';
		$config->rawData = false;
		$config->encryptKeyInfo = 'encryption';
		$config->authKeyInfo = 'authentication';

		$encrypter = Services::encrypter($config);

		$decrypted_data = [];
		foreach($encrypted_data as $key => $value)
		{
			$decrypted_data[$key] = !empty($value) ? $encrypter->decrypt($value): '';
		}

		return $decrypted_data;
	}

	/**
	 * Encrypts data using CI4 algorithms.
	 *
	 * @param array $plain_data Data to be encrypted.
	 * @return array Encrypted data.
	 */
	private function encrypt_data(array $plain_data): array
	{
		$encrypter = Services::encrypter();

		$encrypted_data = [];
		foreach($plain_data as $key => $value)
		{
			$encrypted_data[$key] = !empty($value) ? $encrypter->encrypt($value) : '';
		}

		return $encrypted_data;
	}

	/**
	 * Decrypts data using CI4 algorithms.
	 *
	 * @param array $encrypted_data Data to be decrypted.
	 * @return array Decrypted data.
	 */
	private function decrypt_data(array $encrypted_data): array
	{
		$encrypter = Services::encrypter();

		$decrypted_data = [];
		foreach($encrypted_data as $key => $value)
		{
			$decrypted_data[$key] = !empty($value) ? $encrypter->decrypt($value) : '';
		}

		return $decrypted_data;
	}
}

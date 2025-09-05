<?php

namespace app\Libraries;

use CodeIgniter\Email\Email;
use CodeIgniter\Encryption\Encryption;
use CodeIgniter\Encryption\EncrypterInterface;
use CodeIgniter\Encryption\Exceptions\EncryptionException;
use Config\OSPOS;
use Config\Services;


/**
 * Email library
 *
 * Library with utilities to configure and send emails
 */

class Email_lib
{
    private Email $email;
    private array $config;

    public function __construct()
    {
        $this->email = new Email();
        $this->config = config(OSPOS::class)->settings;

        $encrypter = Services::encrypter();

        $smtp_pass = $this->config['smtp_pass'];
        if (!empty($smtp_pass) && check_encryption()) {
            try {
                $smtp_pass = $encrypter->decrypt($smtp_pass);
            } catch (\EncryptionException $e) {
                // Decryption failed, use the original value
                log_message('error', 'SMTP password decryption failed: ' . $e->getMessage());
                $smtp_pass = '';
            }

        }

        $email_config = [
            'mailType'    => 'html',
            'userAgent'   => 'OSPOS',
            'validate'    => true,
            'protocol'    => $this->config['protocol'],
            'mailPath'    => $this->config['mailpath'],
            'SMTPHost'    => $this->config['smtp_host'],
            'SMTPUser'    => $this->config['smtp_user'],
            'SMTPPass'    => $smtp_pass,
            'SMTPPort'    => (int)$this->config['smtp_port'],
            'SMTPTimeout' => (int)$this->config['smtp_timeout'],
            'SMTPCrypto'  => $this->config['smtp_crypto']
        ];
        $this->email->initialize($email_config);
    }

    /**
     * Email sending function
     * Example of use: $response = sendEmail('john@doe.com', 'Hello', 'This is a message', $filename);
     */
    public function sendEmail(string $to, string $subject, string $message, ?string $attachment = null): bool
    {
        $email = $this->email;

        $email->setFrom($this->config['email'], $this->config['company']);
        $email->setTo($to);
        $email->setSubject($subject);
        $email->setMessage($message);

        if (!empty($attachment)) {
            $email->attach($attachment);
            $email->setAttachmentCID($attachment);
        }

        $result = $email->send();

        if (!$result) {
            error_log($email->printDebugger());
        }

        return $result;
    }
}

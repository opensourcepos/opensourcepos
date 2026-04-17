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
            log_message('error', $email->printDebugger());
        }

        return $result;
    }

    /**
     * Builds an img tag for the company logo to use in email templates.
     *
     * @return string HTML img tag with base64-encoded logo, or empty string if no logo
     */
    public function buildLogoImgTag(): string
    {
        $img_tag = '';
        $logo_path = FCPATH . 'uploads/' . $this->config['company_logo'];

        if (!empty($this->config['company_logo']) && file_exists($logo_path)) {
            $logo_data = base64_encode(file_get_contents($logo_path));
            $img_tag = '<img id="image" src="data:image/png;base64,' . $logo_data . '" alt="company_logo">';
        }

        return $img_tag;
    }

    /**
     * Gets the mime type of the company logo file.
     *
     * @return string|false Mime type or false if logo doesn't exist
     */
    public function getLogoMimeType(): string|false
    {
        $logo_path = FCPATH . 'uploads/' . $this->config['company_logo'];

        if (!empty($this->config['company_logo']) && file_exists($logo_path)) {
            return mime_content_type($logo_path);
        }

        return false;
    }
}

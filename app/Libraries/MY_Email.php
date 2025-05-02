<?php

namespace app\Libraries;

use CodeIgniter\Email\Email;

class MY_Email extends Email
{
    private string $default_cc_address = '';
    private string $default_email_address = '';
    private string $default_sender_name = '';
    private string $default_sender_address = '';
    private string $default_bounce_address = '';

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    /**
     * @param string $subject
     * @param string $body
     * @param string|null $to
     * @param string|null $reply_name
     * @param string|null $reply_mail
     * @param string|null $attachment
     * @return bool
     */
    public function sendMail(string $subject, string $body, ?string $to = null, ?string $reply_name = null, ?string $reply_mail = null, ?string $attachment = null): bool
    {
        $this->setReplyTo($reply_mail, $reply_name);
        $this->setFrom($this->default_sender_address, $this->default_sender_name, $this->default_bounce_address);
        $this->setMailtype('html');
        $this->setSubject($subject);
        $this->setMessage($body);
        if ($to == null) {
            $to = $this->default_email_address;
            $this->setCc($this->default_cc_address);
        }
        if ($attachment) {
            $this->attach($attachment);
        }
        $this->setTo($to);
        return $this->send();
    }
}

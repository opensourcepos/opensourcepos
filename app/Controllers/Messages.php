<?php

namespace App\Controllers;

use App\Libraries\Sms_lib;

use App\Models\Person;
use CodeIgniter\HTTP\ResponseInterface;

class Messages extends Secure_Controller
{
    private Sms_lib $sms_lib;

    public function __construct()
    {
        parent::__construct('messages');

        $this->sms_lib = new Sms_lib();
    }

    /**
     * @return string
     */
    public function getIndex(): string
    {
        return view('messages/sms');
    }

    /**
     * @param int $person_id
     * @return string
     */
    public function getView(int $person_id = NEW_ENTRY): string
    {
        $person = model(Person::class);
        $info = $person->get_info($person_id);

        foreach (get_object_vars($info) as $property => $value) {
            $info->$property = $value;
        }
        $data['person_info'] = $info;

        return view('messages/form_sms', $data);
    }

    /**
     * @return ResponseInterface
     */
    public function send(): ResponseInterface
    {
        $phone   = $this->request->getPost('phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $message = $this->request->getPost('message', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $response = $this->sms_lib->sendSMS($phone, $message);

        if ($response) {
            return $this->response->setJSON(['success' => true, 'message' => lang('Messages.successfully_sent') . ' ' . esc($phone)]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => lang('Messages.unsuccessfully_sent') . ' ' . esc($phone)]);
        }
    }

    /**
     * Sends an SMS message to a user. Used in app/Views/messages/form_sms.php.
     *
     * @param int $person_id
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function send_form(int $person_id = NEW_ENTRY): ResponseInterface
    {
        $phone   = $this->request->getPost('phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $message = $this->request->getPost('message', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $response = $this->sms_lib->sendSMS($phone, $message);

        if ($response) {
            return $this->response->setJSON([
                'success'   => true,
                'message'   => lang('Messages.successfully_sent') . ' ' . esc($phone),
                'person_id' => $person_id
            ]);
        } else {
            return $this->response->setJSON([
                'success'   => false,
                'message'   => lang('Messages.unsuccessfully_sent') . ' ' . esc($phone),
                'person_id' => NEW_ENTRY
            ]);
        }
    }
}

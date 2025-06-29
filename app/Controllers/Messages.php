<?php

namespace App\Controllers;

use App\Libraries\Sms_lib;

use App\Models\Person;

class Messages extends Secure_Controller
{
    private Sms_lib $sms_lib;

    public function __construct()
    {
        parent::__construct('messages');

        $this->sms_lib = new Sms_lib();
    }

    /**
     * @return void
     */
    public function getIndex(): void
    {
        echo view('messages/sms');
    }

    /**
     * @param int $person_id
     * @return void
     */
    public function getView(int $person_id = NEW_ENTRY): void
    {
        $person = model(Person::class);
        $info = $person->get_info($person_id);

        foreach (get_object_vars($info) as $property => $value) {
            $info->$property = $value;
        }
        $data['person_info'] = $info;

        echo view('messages/form_sms', $data);
    }

    /**
     * @return void
     */
    public function send(): void
    {
        $phone   = $this->request->getPost('phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $message = $this->request->getPost('message', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $response = $this->sms_lib->sendSMS($phone, $message);

        if ($response) {
            echo json_encode(['success' => true, 'message' => lang('Messages.successfully_sent') . ' ' . esc($phone)]);
        } else {
            echo json_encode(['success' => false, 'message' => lang('Messages.unsuccessfully_sent') . ' ' . esc($phone)]);
        }
    }

    /**
     * Sends an SMS message to a user. Used in app/Views/messages/form_sms.php.
     *
     * @param int $person_id
     * @return void
     * @noinspection PhpUnused
     */
    public function send_form(int $person_id = NEW_ENTRY): void
    {
        $phone   = $this->request->getPost('phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $message = $this->request->getPost('message', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $response = $this->sms_lib->sendSMS($phone, $message);

        if ($response) {
            echo json_encode([
                'success'   => true,
                'message'   => lang('Messages.successfully_sent') . ' ' . esc($phone),
                'person_id' => $person_id
            ]);
        } else {
            echo json_encode([
                'success'   => false,
                'message'   => lang('Messages.unsuccessfully_sent') . ' ' . esc($phone),
                'person_id' => NEW_ENTRY
            ]);
        }
    }
}

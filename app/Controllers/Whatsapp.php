<?php

namespace App\Controllers;

use App\Libraries\Whatsapp_lib;
use App\Models\Person;
use App\Models\Whatsapp_message;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * WhatsApp messaging controller.
 *
 * Mirrors the SMS Messages controller: a page to send free-form WhatsApp
 * messages plus a per-person modal form, and additionally exposes the full
 * conversation (outbound sends and inbound replies) with a customer.
 */
class Whatsapp extends Secure_Controller
{
    private Whatsapp_lib $whatsapp_lib;
    private Whatsapp_message $whatsapp_message;

    public function __construct()
    {
        parent::__construct('whatsapp');

        $this->whatsapp_lib     = new Whatsapp_lib();
        $this->whatsapp_message = model(Whatsapp_message::class);
    }

    /**
     * Landing page: send form plus the list of recent conversations.
     */
    public function getIndex(): string
    {
        $data['conversations'] = $this->whatsapp_message->get_recent_conversations();
        $data['configured']    = $this->whatsapp_lib->isConfigured();

        return view('whatsapp/whatsapp', $data);
    }

    /**
     * Per-person modal: prefilled form plus that person's conversation thread.
     */
    public function getView(int $person_id = NEW_ENTRY): string
    {
        $person = model(Person::class);
        $info   = $person->get_info($person_id);

        $data['person_info'] = $info;
        $data['phone']       = $this->whatsapp_lib->normalizePhone($info->phone_number ?? '');
        $data['messages']    = $data['phone'] !== ''
            ? $this->whatsapp_message->get_conversation($data['phone'])->getResult()
            : [];

        return view('whatsapp/form_whatsapp', $data);
    }

    /**
     * Returns the conversation thread partial for a phone number (AJAX refresh).
     */
    public function getConversation(string $phone = ''): string
    {
        $phone            = $this->whatsapp_lib->normalizePhone($phone);
        $data['messages'] = $phone !== ''
            ? $this->whatsapp_message->get_conversation($phone)->getResult()
            : [];

        return view('whatsapp/conversation', $data);
    }

    /**
     * Sends a WhatsApp message from the landing page.
     */
    public function postSend(): ResponseInterface
    {
        $phone = $this->request->getPost('phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        // Send the message body verbatim: WhatsApp renders plain text, not HTML.
        // Entity-encoding here would surface literal &amp;/&#039; to the customer
        // and double-encode when the log is later shown via esc().
        $message = trim((string) $this->request->getPost('message'));

        $response = $this->whatsapp_lib->sendText($phone, $message);

        if ($response) {
            return $this->response->setJSON(['success' => true, 'message' => lang('Whatsapp.successfully_sent') . ' ' . esc($phone)]);
        }

        return $this->response->setJSON(['success' => false, 'message' => lang('Whatsapp.unsuccessfully_sent') . ' ' . esc($phone)]);
    }

    /**
     * Sends a WhatsApp message to a specific person. Used in app/Views/whatsapp/form_whatsapp.php.
     *
     * @noinspection PhpUnused
     */
    public function postSend_form(int $person_id = NEW_ENTRY): ResponseInterface
    {
        $phone = $this->request->getPost('phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        // Send the message body verbatim: WhatsApp renders plain text, not HTML.
        // Entity-encoding here would surface literal &amp;/&#039; to the customer
        // and double-encode when the log is later shown via esc().
        $message = trim((string) $this->request->getPost('message'));

        $response = $this->whatsapp_lib->sendText($phone, $message, $person_id === NEW_ENTRY ? null : $person_id);

        if ($response) {
            return $this->response->setJSON([
                'success'   => true,
                'message'   => lang('Whatsapp.successfully_sent') . ' ' . esc($phone),
                'person_id' => $person_id,
            ]);
        }

        return $this->response->setJSON([
            'success'   => false,
            'message'   => lang('Whatsapp.unsuccessfully_sent') . ' ' . esc($phone),
            'person_id' => NEW_ENTRY,
        ]);
    }
}

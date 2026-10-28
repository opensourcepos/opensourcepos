<?php

namespace App\Plugins\WhatsAppPlugin\Controllers;

use App\Controllers\Secure_Controller;
use App\Models\Person;
use App\Plugins\WhatsAppPlugin\Libraries\SaleDocument;
use App\Plugins\WhatsAppPlugin\Libraries\WhatsAppConnector;
use App\Plugins\WhatsAppPlugin\Models\WhatsAppMessage;
use App\Plugins\WhatsAppPlugin\WhatsAppPlugin;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * WhatsApp messaging controller.
 *
 * Mirrors the SMS Messages controller: a page to send free-form WhatsApp messages
 * plus a per-person modal form, and additionally exposes the full conversation
 * (outbound sends and inbound replies) with a customer.
 *
 * Guarded by the 'whatsapp' permission registered in WhatsAppPlugin::install().
 */
class WhatsAppController extends Secure_Controller
{
    private WhatsAppPlugin $plugin;
    private WhatsAppConnector $connector;
    private WhatsAppMessage $messageModel;

    public function __construct()
    {
        parent::__construct('whatsapp');

        $plugin = service('pluginManager')->getPlugin('whatsapp');

        // Routes are registered for every plugin directory, enabled or not, so a
        // disabled plugin must refuse to serve rather than rely on the route
        // simply not existing.
        if (! $plugin instanceof WhatsAppPlugin || ! $plugin->isEnabled()) {
            throw PageNotFoundException::forPageNotFound();
        }

        $this->plugin       = $plugin;
        $this->connector    = $plugin->connector();
        $this->messageModel = model(WhatsAppMessage::class);
    }

    public function getIndex(): string
    {
        return $this->plugin->renderPluginView('whatsapp', [
            'conversations' => $this->messageModel->getRecentConversations(),
            'configured'    => $this->connector->isConfigured(),
            'saved_message' => $this->plugin->savedMessage(),
        ]);
    }

    /**
     * Per-person modal: prefilled form plus that person's conversation thread.
     */
    public function getView(int $personId = NEW_ENTRY): string
    {
        $info  = model(Person::class)->getInfo($personId);
        $phone = $this->connector->normalizePhone($info->phone_number ?? '');

        return $this->plugin->renderPluginView('form_whatsapp', [
            'person_info'   => $info,
            'phone'         => $phone,
            'saved_message' => $this->plugin->savedMessage(),
            'messages'      => $phone !== ''
                ? $this->messageModel->getConversation($phone)->getResult()
                : [],
        ]);
    }

    /**
     * Returns the conversation thread partial for a phone number (AJAX refresh).
     */
    public function getConversation(string $phone = ''): string
    {
        $phone = $this->connector->normalizePhone($phone);

        return $this->plugin->renderPluginView('conversation', [
            'messages' => $phone !== ''
                ? $this->messageModel->getConversation($phone)->getResult()
                : [],
        ]);
    }

    public function postSend(): ResponseInterface
    {
        $phone = $this->request->getPost('phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        // Send the message body verbatim: WhatsApp renders plain text, not HTML.
        // Entity-encoding here would surface literal &amp;/&#039; to the customer
        // and double-encode when the log is later shown via esc().
        $message = trim((string) $this->request->getPost('message'));

        $sent = $this->connector->sendText((string) $phone, $message);

        return $this->response->setJSON([
            'success' => $sent,
            'message' => lang($sent ? 'WhatsAppPlugin.successfully_sent' : 'WhatsAppPlugin.unsuccessfully_sent') . ' ' . esc($phone),
        ]);
    }

    /**
     * Used in Views/form_whatsapp.php.
     *
     * @noinspection PhpUnused
     */
    public function postSendForm(int $personId = NEW_ENTRY): ResponseInterface
    {
        $phone = $this->request->getPost('phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        // See postSend() for why the body is not escaped here.
        $message = trim((string) $this->request->getPost('message'));

        $sent = $this->connector->sendText(
            (string) $phone,
            $message,
            $personId === NEW_ENTRY ? null : $personId,
        );

        return $this->response->setJSON([
            'success'   => $sent,
            'message'   => lang($sent ? 'WhatsAppPlugin.successfully_sent' : 'WhatsAppPlugin.unsuccessfully_sent') . ' ' . esc($phone),
            'person_id' => $sent ? $personId : NEW_ENTRY,
        ]);
    }

    /**
     * Sends a sale document (invoice/quote/work order/receipt) as a PDF over
     * WhatsApp. Triggered by the button injected into the sale document views.
     *
     * @noinspection PhpUnused
     */
    public function postSendDocument(int $saleId, string $type = 'invoice'): ResponseInterface
    {
        // $type is interpolated into a view path, so restrict it to known types.
        if (! in_array($type, WhatsAppPlugin::DOCUMENT_TYPES, true)) {
            $type = 'invoice';
        }

        $saleDocument = new SaleDocument();

        // Checked before rendering so a missing phone number and a failed PDF are
        // never reported as each other.
        $phone = $this->connector->normalizePhone($saleDocument->customerPhone($saleId));

        if ($phone === '') {
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('WhatsAppPlugin.no_phone'),
                'id'      => $saleId,
            ]);
        }

        $document = $saleDocument->renderPdf($saleId, $type);

        if ($document === null) {
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('WhatsAppPlugin.document_failed'),
                'id'      => $saleId,
            ]);
        }

        $sent = $this->connector->sendDocument(
            $document['phone'],
            $document['path'],
            $document['display_name'],
            $document['caption'],
            $document['person_id'],
        );

        if (is_file($document['path'])) {
            unlink($document['path']);
        }

        return $this->response->setJSON([
            'success' => $sent,
            'message' => lang('WhatsAppPlugin.' . $type . ($sent ? '_sent' : '_unsent')) . ' ' . $document['phone'],
            'id'      => $saleId,
        ]);
    }
}

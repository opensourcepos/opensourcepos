<?php

namespace App\Plugins\WhatsappPlugin\Controllers;

use App\Controllers\Secure_Controller;
use App\Models\Person;
use App\Plugins\WhatsappPlugin\Libraries\SaleDocument;
use App\Plugins\WhatsappPlugin\Libraries\WhatsappConnector;
use App\Plugins\WhatsappPlugin\Models\WhatsappMessage;
use App\Plugins\WhatsappPlugin\WhatsappPlugin;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * WhatsApp messaging controller.
 *
 * Mirrors the SMS Messages controller: a page to send free-form WhatsApp
 * messages plus a per-person modal form, and additionally exposes the full
 * conversation (outbound sends and inbound replies) with a customer.
 *
 * Guarded by the 'whatsapp' permission registered in WhatsappPlugin::install().
 */
class WhatsappController extends Secure_Controller
{
    private WhatsappPlugin $plugin;
    private WhatsappConnector $connector;
    private WhatsappMessage $messageModel;

    public function __construct()
    {
        parent::__construct('whatsapp');

        $plugin = service('pluginManager')->getPlugin('whatsapp');

        // Plugin namespaces — and therefore these routes — are registered for every
        // plugin directory, enabled or not. A disabled plugin must not send messages
        // or expose conversation history, so refuse to serve rather than rely on the
        // route simply not existing.
        if (! $plugin instanceof WhatsappPlugin || ! $plugin->isEnabled()) {
            throw PageNotFoundException::forPageNotFound();
        }

        $this->plugin       = $plugin;
        $this->connector    = $plugin->connector();
        $this->messageModel = model(WhatsappMessage::class);
    }

    /**
     * Landing page: send form plus the list of recent conversations.
     */
    public function getIndex(): string
    {
        return $this->plugin->renderPluginView('whatsapp', [
            'conversations' => $this->messageModel->get_recent_conversations(),
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
                ? $this->messageModel->get_conversation($phone)->getResult()
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
                ? $this->messageModel->get_conversation($phone)->getResult()
                : [],
        ]);
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

        $sent = $this->connector->sendText((string) $phone, $message);

        return $this->response->setJSON([
            'success' => $sent,
            'message' => lang($sent ? 'WhatsappPlugin.successfully_sent' : 'WhatsappPlugin.unsuccessfully_sent') . ' ' . esc($phone),
        ]);
    }

    /**
     * Sends a WhatsApp message to a specific person. Used in Views/form_whatsapp.php.
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
            'message'   => lang($sent ? 'WhatsappPlugin.successfully_sent' : 'WhatsappPlugin.unsuccessfully_sent') . ' ' . esc($phone),
            'person_id' => $sent ? $personId : NEW_ENTRY,
        ]);
    }

    /**
     * Sends a sale document (invoice/quote/work order/receipt) as a PDF over
     * WhatsApp. Triggered by the button injected into the sale document views.
     *
     * @noinspection PhpUnused
     */
    public function getSendDocument(int $saleId, string $type = 'invoice'): ResponseInterface
    {
        // $type is interpolated into a view path, so restrict it to known types.
        if (! in_array($type, WhatsappPlugin::DOCUMENT_TYPES, true)) {
            $type = 'invoice';
        }

        $saleDocument = new SaleDocument();
        $document     = $saleDocument->renderPdf($saleId, $type);

        if ($document === null) {
            $saleDocument->clearCart();

            return $this->response->setJSON([
                'success' => false,
                'message' => lang('WhatsappPlugin.no_phone'),
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

        // Always clean up the temp PDF.
        if (is_file($document['path'])) {
            unlink($document['path']);
        }

        $saleDocument->clearCart();

        return $this->response->setJSON([
            'success' => $sent,
            'message' => lang('WhatsappPlugin.' . $type . ($sent ? '_sent' : '_unsent')) . ' ' . $document['phone'],
            'id'      => $saleId,
        ]);
    }
}

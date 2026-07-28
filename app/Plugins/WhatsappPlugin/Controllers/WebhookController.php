<?php

namespace App\Plugins\WhatsappPlugin\Controllers;

use App\Controllers\BaseController;
use App\Plugins\WhatsappPlugin\Models\WhatsappMessage;
use App\Plugins\WhatsappPlugin\WhatsappPlugin;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

/**
 * Public WhatsApp webhook endpoint (no authentication).
 *
 * Meta calls this URL to (1) verify the subscription (GET) and (2) deliver
 * inbound messages and delivery-status updates (POST). Received messages are
 * stored in the conversation log so they appear alongside outbound messages.
 *
 * The route is `plugins/whatsapp/webhook`, which app/Config/Filters.php exempts
 * from CSRF via its plugin webhook pattern. It must be publicly reachable over
 * HTTPS for Meta to deliver events. Because it is unauthenticated, every POST is
 * verified against the app secret before anything is persisted.
 *
 * @see https://developers.facebook.com/documentation/business-messaging/whatsapp/get-started
 */
class WebhookController extends BaseController
{
    private WhatsappPlugin $plugin;

    public function __construct()
    {
        $plugin = service('pluginManager')->getPlugin('whatsapp');

        // Routes exist for disabled plugins too — do not accept deliveries then.
        if (! $plugin instanceof WhatsappPlugin || ! $plugin->isEnabled()) {
            throw PageNotFoundException::forPageNotFound();
        }

        $this->plugin = $plugin;
    }

    /**
     * Handles both the verification handshake (GET) and event delivery (POST).
     */
    public function index(): ResponseInterface
    {
        if ($this->request->getMethod() === 'GET') {
            return $this->verify();
        }

        return $this->receive();
    }

    /**
     * Verification handshake: echoes hub.challenge when the verify token matches.
     */
    private function verify(): ResponseInterface
    {
        $mode      = $this->request->getGet('hub_mode');
        $token     = $this->request->getGet('hub_verify_token');
        $challenge = $this->request->getGet('hub_challenge');

        $expected = (string) $this->plugin->getSettings()['verify_token'];

        if ($mode === 'subscribe' && $expected !== '' && hash_equals($expected, (string) $token)) {
            // text/plain so the dev debug toolbar does not inject HTML into the
            // challenge echo, which Meta compares byte-for-byte.
            return $this->response->setStatusCode(200)->setContentType('text/plain')->setBody((string) $challenge);
        }

        return $this->response->setStatusCode(403)->setContentType('text/plain')->setBody('Forbidden');
    }

    /**
     * Receives inbound messages and status updates and stores them.
     *
     * Always returns 200 so Meta does not retry on our own processing errors.
     */
    private function receive(): ResponseInterface
    {
        $raw       = $this->request->getBody() ?? '';
        $appSecret = $this->plugin->secret('app_secret');

        // Signature verification is mandatory. Without an app secret we cannot
        // authenticate the sender on this public, CSRF-exempt endpoint, so we
        // refuse to persist anything rather than fail open to spoofed payloads.
        if ($appSecret === '') {
            log_message('warning', 'WhatsApp webhook: app secret not configured; rejecting inbound payload.');

            return $this->acknowledge();
        }

        if (! $this->signatureValid($raw, $appSecret)) {
            log_message('warning', 'WhatsApp webhook: invalid signature, ignoring payload.');

            return $this->acknowledge();
        }

        try {
            $payload = json_decode($raw, true);
            $this->process(is_array($payload) ? $payload : []);
        } catch (Throwable $e) {
            log_message('error', 'WhatsApp webhook processing error: ' . $e->getMessage());
        }

        return $this->acknowledge();
    }

    /**
     * Walks the webhook payload storing inbound messages and status updates.
     */
    private function process(array $payload): void
    {
        $messageModel = model(WhatsappMessage::class);
        $connector    = $this->plugin->connector();

        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $value = $change['value'] ?? [];

                // Inbound messages from customers.
                foreach ($value['messages'] ?? [] as $message) {
                    $phone = $connector->normalizePhone((string) ($message['from'] ?? ''));

                    if ($phone === '') {
                        continue;
                    }

                    $type = (string) ($message['type'] ?? 'text');

                    $messageModel->log([
                        'person_id'     => null,
                        'phone'         => $phone,
                        'direction'     => 'in',
                        'type'          => $type,
                        'body'          => $this->extractInboundBody($message, $type),
                        'wa_message_id' => $message['id'] ?? null,
                        'status'        => 'received',
                        'created_at'    => isset($message['timestamp'])
                            ? date('Y-m-d H:i:s', (int) $message['timestamp'])
                            : date('Y-m-d H:i:s'),
                    ]);
                }

                // Delivery/read status updates for our outbound messages.
                foreach ($value['statuses'] ?? [] as $status) {
                    if (! empty($status['id']) && ! empty($status['status'])) {
                        $messageModel->update_status((string) $status['id'], (string) $status['status']);
                    }
                }
            }
        }
    }

    /**
     * Extracts a human-readable body from an inbound message of any type.
     */
    private function extractInboundBody(array $message, string $type): string
    {
        return match ($type) {
            'text'   => (string) ($message['text']['body'] ?? ''),
            'button' => (string) ($message['button']['text'] ?? ''),
            'image', 'document', 'video', 'audio' => (string) ($message[$type]['caption'] ?? ''),
            default => '',
        };
    }

    /**
     * Validates the X-Hub-Signature-256 header against the app secret.
     * Fails closed: an empty secret or missing/mismatched header is rejected.
     */
    private function signatureValid(string $raw, string $appSecret): bool
    {
        if ($appSecret === '') {
            return false;
        }

        $header = $this->request->getHeaderLine('X-Hub-Signature-256');

        if ($header === '' || ! str_starts_with($header, 'sha256=')) {
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $raw, $appSecret);

        return hash_equals($expected, $header);
    }

    /**
     * Empty 200 response: tells Meta the delivery was received so it is not retried.
     */
    private function acknowledge(): ResponseInterface
    {
        return $this->response->setStatusCode(200)->setContentType('text/plain')->setBody('');
    }
}

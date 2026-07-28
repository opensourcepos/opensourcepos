<?php

namespace App\Plugins\WhatsappPlugin\Libraries;

use App\Plugins\WhatsappPlugin\Models\WhatsappMessage;
use CodeIgniter\HTTP\CURLRequest;
use Config\Services;
use CURLFile;
use Throwable;

/**
 * Sends messages through the WhatsApp Business Cloud API (Meta / Graph API) and
 * records every outbound message in the conversation log.
 *
 * Credentials are injected by WhatsappPlugin::connector() rather than read from
 * global config, so the connector stays usable in isolation and in tests.
 *
 * @see https://developers.facebook.com/documentation/business-messaging/whatsapp/get-started
 */
class WhatsappConnector
{
    private array $settings;
    private WhatsappMessage $messageModel;

    /**
     * @param array $settings enabled, api_url, api_version, phone_id, token,
     *                        default_country_code
     */
    public function __construct(array $settings = [])
    {
        $this->settings     = $settings;
        $this->messageModel = model(WhatsappMessage::class);
    }

    /**
     * Sends a free-form text message.
     *
     * Note: free-form messages are only delivered within the 24 hour customer
     * service window (i.e. after the customer has messaged the business).
     * Outside that window an approved message template is required.
     *
     * @param string   $phone    Recipient phone number (any format; normalized here).
     * @param string   $message  Message body.
     * @param int|null $personId Customer person_id, when known (for the log).
     *
     * @return bool True when the API accepted the message.
     */
    public function sendText(string $phone, string $message, ?int $personId = null): bool
    {
        $to = $this->normalizePhone($phone);

        if (! $this->isConfigured() || $to === '' || $message === '') {
            $this->logOutbound($to, 'text', $message, null, null, null, 'failed', lang('WhatsappPlugin.not_configured'), $personId);

            return false;
        }

        $response = $this->post($this->messagesUrl(), [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $to,
            'type'              => 'text',
            'text'              => ['preview_url' => false, 'body' => $message],
        ]);

        $waMessageId = $this->extractMessageId($response);
        $success     = $waMessageId !== null;

        $this->logOutbound(
            $to,
            'text',
            $message,
            null,
            null,
            $waMessageId,
            $success ? 'sent' : 'failed',
            $success ? null : $this->extractError($response),
            $personId,
        );

        return $success;
    }

    /**
     * Sends a document (e.g. a PDF invoice) as an attachment.
     *
     * The local file is uploaded to the WhatsApp media endpoint first, then a
     * document message referencing the returned media id is sent.
     *
     * @param string   $phone    Recipient phone number (any format; normalized here).
     * @param string   $filepath Absolute path to the local file to send.
     * @param string   $filename Filename shown to the recipient.
     * @param string   $caption  Optional caption.
     * @param int|null $personId Customer person_id, when known (for the log).
     *
     * @return bool True when the API accepted the message.
     */
    public function sendDocument(string $phone, string $filepath, string $filename, string $caption = '', ?int $personId = null): bool
    {
        $to = $this->normalizePhone($phone);

        if (! $this->isConfigured() || $to === '' || ! is_file($filepath)) {
            $this->logOutbound($to, 'document', $caption, null, $filename, null, 'failed', lang('WhatsappPlugin.not_configured'), $personId);

            return false;
        }

        $mediaId = $this->uploadMedia($filepath, 'application/pdf');

        if ($mediaId === null) {
            $this->logOutbound($to, 'document', $caption, null, $filename, null, 'failed', lang('WhatsappPlugin.media_upload_failed'), $personId);

            return false;
        }

        $document = ['id' => $mediaId, 'filename' => $filename];

        if ($caption !== '') {
            $document['caption'] = $caption;
        }

        $response = $this->post($this->messagesUrl(), [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $to,
            'type'              => 'document',
            'document'          => $document,
        ]);

        $waMessageId = $this->extractMessageId($response);
        $success     = $waMessageId !== null;

        $this->logOutbound(
            $to,
            'document',
            $caption,
            $mediaId,
            $filename,
            $waMessageId,
            $success ? 'sent' : 'failed',
            $success ? null : $this->extractError($response),
            $personId,
        );

        return $success;
    }

    /**
     * Normalizes a phone number to the digits-only E.164 form the API expects
     * (no '+', spaces or punctuation). When a default country code is configured
     * and the number does not already start with it, it is prepended.
     *
     * @param string $phone Raw phone number.
     *
     * @return string Normalized number, or '' when no digits are present.
     */
    public function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return '';
        }

        $country = preg_replace('/\D+/', '', (string) ($this->settings['default_country_code'] ?? '')) ?? '';

        if ($country !== '' && ! str_starts_with($digits, $country)) {
            // Drop a single leading trunk '0' before prefixing the country code.
            $digits = $country . ltrim($digits, '0');
        }

        return $digits;
    }

    /**
     * @return bool True when the plugin is enabled and the required credentials are set.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->settings['enabled'])
            && ! empty($this->settings['phone_id'])
            && $this->token() !== '';
    }

    /**
     * Uploads a local file to the WhatsApp media endpoint.
     *
     * @param string $filepath Absolute path to the file.
     * @param string $mime     Mime type of the file.
     *
     * @return string|null The media id, or null on failure.
     */
    private function uploadMedia(string $filepath, string $mime): ?string
    {
        try {
            $response = $this->client()->post($this->mediaUrl(), [
                'headers'   => ['Authorization' => 'Bearer ' . $this->token()],
                'multipart' => [
                    'messaging_product' => 'whatsapp',
                    'type'              => $mime,
                    'file'              => new CURLFile($filepath, $mime, basename($filepath)),
                ],
            ]);

            $body = json_decode((string) $response->getBody(), true);

            if (is_array($body) && ! empty($body['id'])) {
                return (string) $body['id'];
            }

            log_message('error', 'WhatsApp media upload failed: ' . (string) $response->getBody());
        } catch (Throwable $e) {
            log_message('error', 'WhatsApp media upload exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Sends a JSON POST to a Graph API endpoint with the bearer token.
     *
     * @param string $url     Full endpoint URL.
     * @param array  $payload JSON payload.
     *
     * @return array|null Decoded response body, or null on transport failure.
     */
    private function post(string $url, array $payload): ?array
    {
        try {
            $response = $this->client()->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token(),
                    'Content-Type'  => 'application/json',
                ],
                'body' => json_encode($payload),
            ]);

            $body = json_decode((string) $response->getBody(), true);

            return is_array($body) ? $body : null;
        } catch (Throwable $e) {
            log_message('error', 'WhatsApp API exception: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Records an outbound message in the conversation log.
     */
    private function logOutbound(string $phone, string $type, ?string $body, ?string $mediaId, ?string $filename, ?string $waMessageId, string $status, ?string $error, ?int $personId): void
    {
        try {
            $this->messageModel->log([
                'person_id'     => $personId,
                'phone'         => $phone,
                'direction'     => 'out',
                'type'          => $type,
                'body'          => $body,
                'media_id'      => $mediaId,
                'filename'      => $filename,
                'wa_message_id' => $waMessageId,
                'status'        => $status,
                'error'         => $error,
            ]);
        } catch (Throwable $e) {
            log_message('error', 'WhatsApp log write failed: ' . $e->getMessage());
        }
    }

    private function token(): string
    {
        return (string) ($this->settings['token'] ?? '');
    }

    private function client(): CURLRequest
    {
        return Services::curlrequest([
            'timeout'     => 15,
            'http_errors' => false,
        ]);
    }

    private function apiBase(): string
    {
        $url     = rtrim((string) ($this->settings['api_url'] ?? 'https://graph.facebook.com'), '/');
        $version = trim((string) ($this->settings['api_version'] ?? 'v21.0'), '/');

        return $url . '/' . $version;
    }

    private function messagesUrl(): string
    {
        return $this->apiBase() . '/' . $this->settings['phone_id'] . '/messages';
    }

    private function mediaUrl(): string
    {
        return $this->apiBase() . '/' . $this->settings['phone_id'] . '/media';
    }

    private function extractMessageId(?array $response): ?string
    {
        return $response['messages'][0]['id'] ?? null;
    }

    private function extractError(?array $response): ?string
    {
        return $response['error']['message'] ?? lang('WhatsappPlugin.send_failed');
    }
}

<?php

namespace App\Plugins\WhatsappPlugin;

use App\Libraries\Plugins\BasePlugin;
use App\Models\PluginMigrationModel;
use App\Plugins\WhatsappPlugin\Libraries\SaleDocument;
use App\Plugins\WhatsappPlugin\Libraries\WhatsappConnector;
use CodeIgniter\Events\Events;
use Config\Database;
use Config\Services;
use Throwable;

/**
 * Plugin that sends WhatsApp messages through the WhatsApp Business Cloud API
 * (Meta / Graph API) and records the resulting conversation.
 *
 * Provides an office page for free-form messaging plus a "Send via WhatsApp"
 * button on each sale document, injected through the sales view hooks.
 *
 * Copyright (C) 2026 opensourcepos.org
 *
 * @see https://developers.facebook.com/documentation/business-messaging/whatsapp/get-started
 */
class WhatsappPlugin extends BasePlugin
{
    /**
     * Settings holding secrets. Stored encrypted, decrypted on read.
     */
    private const ENCRYPTED_SETTINGS = ['token', 'app_secret'];

    /**
     * Sale document types this plugin can deliver. Also guards the $type route
     * segment, which is interpolated into a view path.
     */
    public const DOCUMENT_TYPES = ['invoice', 'quote', 'work_order', 'receipt'];

    public function getPluginId(): string
    {
        return 'whatsapp';
    }

    public function getPluginName(): string
    {
        return 'WhatsApp';
    }

    public function getPluginDescription(): string
    {
        return lang('WhatsappPlugin.description');
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function registerEvents(): void
    {
        // One callback for all four sale documents; the hook name carries the type.
        Events::on('view:sales_receipt_buttons', fn (array $data) => $this->injectSaleDocumentButton('receipt', $data));
        Events::on('view:sales_invoice_buttons', fn (array $data) => $this->injectSaleDocumentButton('invoice', $data));
        Events::on('view:sales_quote_buttons', fn (array $data) => $this->injectSaleDocumentButton('quote', $data));
        Events::on('view:sales_work_order_buttons', fn (array $data) => $this->injectSaleDocumentButton('work_order', $data));

        log_message('debug', 'WhatsApp plugin events registered');
    }

    public function install(): bool
    {
        log_message('info', 'Installing WhatsApp plugin');

        $this->setSetting('api_url', 'https://graph.facebook.com');
        $this->setSetting('api_version', 'v21.0');
        $this->setSetting('phone_id', '');
        $this->setSetting('business_id', '');
        $this->setSetting('token', '');
        $this->setSetting('default_country_code', '');
        $this->setSetting('saved_message', '');
        $this->setSetting('verify_token', '');
        $this->setSetting('app_secret', '');

        // Office menu entry plus the permission guarding the messaging page.
        $this->registerModule('whatsapp', 101);

        return true;
    }

    /**
     * Uninstall is documented as destructive: leave the database exactly as it was
     * before install, so the conversation log is dropped here.
     *
     * The migration version is also reset, otherwise a later re-install would skip
     * CreateWhatsappMessagesTable and leave the plugin without its table.
     */
    public function uninstall(): bool
    {
        log_message('info', 'Uninstalling WhatsApp plugin');

        $this->unregisterModule('whatsapp');

        Database::forge()->dropTable('whatsapp_messages', true);

        (new PluginMigrationModel())->setVersion($this->getPluginId(), 0);

        return true;
    }

    public function getConfigView(): ?string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'config';
    }

    /**
     * Settings for the config view. Secrets are decrypted so the form can show
     * the stored value; everything else is returned as persisted.
     */
    public function getSettings(): array
    {
        return [
            'api_url'              => $this->getSetting('api_url', 'https://graph.facebook.com'),
            'api_version'          => $this->getSetting('api_version', 'v21.0'),
            'phone_id'             => $this->getSetting('phone_id', ''),
            'business_id'          => $this->getSetting('business_id', ''),
            'token'                => $this->decryptSetting((string) $this->getSetting('token', '')),
            'default_country_code' => $this->getSetting('default_country_code', ''),
            'saved_message'        => $this->getSetting('saved_message', ''),
            'verify_token'         => $this->getSetting('verify_token', ''),
            'app_secret'           => $this->decryptSetting((string) $this->getSetting('app_secret', '')),
            'enabled'              => $this->getSetting('enabled', '0'),
        ];
    }

    /**
     * Extra view-only data: the webhook URL the merchant must register with Meta.
     */
    public function getConfigViewData(): array
    {
        return ['webhook_url' => site_url('plugins/whatsapp/webhook')];
    }

    /**
     * Persists settings, encrypting secrets. An empty secret submission is
     * treated as "clear", matching the behaviour of the config form.
     */
    public function saveSettings(array $settings): bool
    {
        $normalized = [];

        foreach ($settings as $key => $value) {
            if (in_array($key, self::ENCRYPTED_SETTINGS, true)) {
                $raw              = (string) $value;
                $normalized[$key] = $raw !== '' ? $this->encryptSetting($raw) : '';

                continue;
            }

            $normalized[$key] = (string) $value;
        }

        return parent::saveSettings($normalized);
    }

    /**
     * Builds a connector primed with the current credentials.
     */
    public function connector(): WhatsappConnector
    {
        $settings = $this->getSettings();

        return new WhatsappConnector([
            'enabled'              => $this->isEnabled(),
            'api_url'              => $settings['api_url'],
            'api_version'          => $settings['api_version'],
            'phone_id'             => $settings['phone_id'],
            'token'                => $settings['token'],
            'default_country_code' => $settings['default_country_code'],
        ]);
    }

    /**
     * Returns a single decrypted setting by key, for callers (such as the webhook
     * controller) that need one secret without building the whole settings array.
     */
    public function secret(string $key): string
    {
        return $this->decryptSetting((string) $this->getSetting($key, ''));
    }

    /**
     * Message pre-filled in the send forms.
     */
    public function savedMessage(): string
    {
        return (string) $this->getSetting('saved_message', '');
    }

    /**
     * Renders one of this plugin's views.
     *
     * BasePlugin::renderView() is protected, so this exposes it for the plugin's
     * own controllers.
     */
    public function renderPluginView(string $viewName, array $data = []): string
    {
        return $this->renderView($viewName, $data);
    }

    /**
     * Renders the "Send via WhatsApp" button into a sale document view.
     *
     * Only `saleId` is passed by the hook, so the recipient's phone number is
     * resolved here; nothing is rendered when the customer has no number.
     *
     * @param string $type One of self::DOCUMENT_TYPES.
     * @param array  $data Hook data, containing 'saleId'.
     */
    private function injectSaleDocumentButton(string $type, array $data): void
    {
        $saleId = (int) ($data['saleId'] ?? 0);

        if ($saleId === 0) {
            return;
        }

        $phone = $this->connector()->normalizePhone(
            (new SaleDocument())->customerPhone($saleId),
        );

        if ($phone === '') {
            return;
        }

        echo $this->renderView('sale_document_button', ['saleId' => $saleId, 'documentType' => $type]);
    }

    private function encryptSetting(string $value): string
    {
        try {
            return base64_encode(Services::encrypter()->encrypt($value));
        } catch (Throwable $e) {
            log_message('error', 'WhatsApp plugin: could not encrypt setting: ' . $e->getMessage());

            return $value;
        }
    }

    /**
     * Decrypts a stored secret, tolerating values written before encryption was
     * available (mirrors MailchimpPlugin::decryptApiKey()).
     */
    private function decryptSetting(string $value): string
    {
        if ($value === '') {
            return '';
        }

        try {
            $decoded = base64_decode($value, true);
            if ($decoded !== false) {
                return Services::encrypter()->decrypt($decoded);
            }
        } catch (Throwable) {
            // Legacy plaintext or an old binary-encrypted value — fall through.
        }

        return $value;
    }
}

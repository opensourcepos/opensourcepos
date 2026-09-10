<?php

namespace App\Plugins\WhatsAppPlugin;

use App\Libraries\Plugins\BasePlugin;
use App\Models\PluginMigrationModel;
use App\Plugins\WhatsAppPlugin\Libraries\SaleDocument;
use App\Plugins\WhatsAppPlugin\Libraries\WhatsAppConnector;
use CodeIgniter\Events\Events;
use Config\Database;
use Config\Services;
use Throwable;

/**
 * Plugin that sends WhatsApp messages through the WhatsApp Business Cloud API
 * (Meta / Graph API) and records the resulting conversation.
 *
 * Copyright (c) 2026 Joshua Fernandez (aka joshua1234511)
 *
 * @see https://developers.facebook.com/documentation/business-messaging/whatsapp/get-started
 */
class WhatsAppPlugin extends BasePlugin
{
    /**
     * Doubles as the allowlist for saveSettings(), so a crafted form post cannot
     * write keys the plugin does not own.
     */
    private const DEFAULT_SETTINGS = [
        'api_url'              => 'https://graph.facebook.com',
        'api_version'          => 'v21.0',
        'phone_id'             => '',
        'business_id'          => '',
        'token'                => '',
        'default_country_code' => '',
        'saved_message'        => '',
        'verify_token'         => '',
        'app_secret'           => '',
    ];

    private const ENCRYPTED_SETTINGS = ['token', 'app_secret'];

    /**
     * api_url builds every outbound call, so an unrestricted one would let anyone
     * who can save settings aim authenticated server-side requests at any host.
     */
    private const ALLOWED_API_HOSTS = ['graph.facebook.com'];

    /**
     * Also guards the $type route segment, which is interpolated into a view path.
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
        return lang('WhatsAppPlugin.description');
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

        Events::on('view:module_icon_whatsapp', [$this, 'injectModuleIcon']);
    }

    public function install(): bool
    {
        $this->log('info', 'Installing WhatsApp plugin');

        foreach (self::DEFAULT_SETTINGS as $key => $default) {
            $this->setSetting($key, $default);
        }

        $this->registerModule('whatsapp', 101);

        return true;
    }

    /**
     * Uninstall is documented as destructive: leave the database exactly as it was
     * before install, so the conversation log is dropped here.
     *
     * The migration version is also reset, otherwise a later re-install would skip
     * CreateWhatsAppMessagesTable and leave the plugin without its table.
     */
    public function uninstall(): bool
    {
        $this->log('info', 'Uninstalling WhatsApp plugin');

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
     * Settings for the config form. This array is rendered into the browser, so
     * the secrets are replaced by a flag saying whether each one is set; writing
     * a decrypted token into the DOM would undo storing it encrypted.
     */
    public function getSettings(): array
    {
        $settings = $this->allSettings();

        $settings['token_configured']      = $settings['token'] !== '';
        $settings['app_secret_configured'] = $settings['app_secret'] !== '';
        $settings['token']                 = '';
        $settings['app_secret']            = '';

        return $settings;
    }

    /**
     * Includes the decrypted secrets, for callers that talk to the API. Never
     * hand this to a view.
     */
    private function allSettings(): array
    {
        $settings = [];

        foreach (self::DEFAULT_SETTINGS as $key => $default) {
            $value = (string) $this->getSetting($key, $default);

            $settings[$key] = in_array($key, self::ENCRYPTED_SETTINGS, true)
                ? $this->decryptSetting($value)
                : $value;
        }

        return $settings;
    }

    public function getConfigViewData(): array
    {
        return ['webhook_url' => site_url('plugins/whatsapp/webhook')];
    }

    /**
     * Persists settings, encrypting secrets.
     *
     * The form never renders a stored secret, so an empty secret field means
     * "unchanged"; clearing one is the separate clear_* checkbox. Nothing is
     * persisted when a secret cannot be encrypted, so a failing encrypter can
     * never downgrade a stored token to plaintext.
     */
    public function saveSettings(array $settings): bool
    {
        $normalized = [];

        foreach ($settings as $key => $value) {
            if (! array_key_exists($key, self::DEFAULT_SETTINGS)) {
                continue;
            }

            $raw = (string) $value;

            if (in_array($key, self::ENCRYPTED_SETTINGS, true)) {
                if (! empty($settings['clear_' . $key])) {
                    $normalized[$key] = '';

                    continue;
                }

                if ($raw === '') {
                    continue;
                }

                $encrypted = $this->encryptSetting($raw);

                if ($encrypted === null) {
                    return false;
                }

                $normalized[$key] = $encrypted;

                continue;
            }

            if ($key === 'api_url') {
                $raw = rtrim(trim($raw), '/');

                if ($raw === '') {
                    $raw = self::DEFAULT_SETTINGS['api_url'];
                }

                if (! $this->apiUrlAllowed($raw)) {
                    $this->log('warning', 'Refusing to store an API base URL outside the WhatsApp Cloud API: ' . $raw);

                    return false;
                }
            }

            $normalized[$key] = $raw;
        }

        return parent::saveSettings($normalized);
    }

    public function connector(): WhatsAppConnector
    {
        $settings = $this->allSettings();

        return new WhatsAppConnector([
            'enabled'              => $this->isEnabled(),
            'api_url'              => $settings['api_url'],
            'api_version'          => $settings['api_version'],
            'phone_id'             => $settings['phone_id'],
            'token'                => $settings['token'],
            'default_country_code' => $settings['default_country_code'],
        ]);
    }

    /**
     * Returns a single decrypted setting, for callers that need one secret without
     * building the whole settings array.
     */
    public function secret(string $key): string
    {
        return $this->decryptSetting((string) $this->getSetting($key, ''));
    }

    public function savedMessage(): string
    {
        return (string) $this->getSetting('saved_message', '');
    }

    /**
     * BasePlugin::renderView() is protected, so this exposes it for the plugin's
     * own controllers.
     */
    public function renderPluginView(string $viewName, array $data = []): string
    {
        return $this->renderView($viewName, $data);
    }

    public function injectModuleIcon(): void
    {
        echo $this->renderView('module_icon');
    }

    /**
     * Renders the "Send via WhatsApp" button into a sale document view.
     *
     * Only `saleId` is passed by the hook, so the recipient's phone number is
     * resolved here; nothing is rendered when the customer has no number.
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

    private function apiUrlAllowed(string $url): bool
    {
        $parts = parse_url($url);

        return ($parts['scheme'] ?? '') === 'https'
            && in_array(strtolower($parts['host'] ?? ''), self::ALLOWED_API_HOSTS, true);
    }

    /**
     * @return string|null Null when the value could not be encrypted.
     */
    private function encryptSetting(string $value): ?string
    {
        try {
            return base64_encode(Services::encrypter()->encrypt($value));
        } catch (Throwable $e) {
            $this->log('critical', 'Could not encrypt setting; refusing to store it as plaintext: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Tolerates values written before encryption was available (mirrors
     * MailchimpPlugin::decryptApiKey()).
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

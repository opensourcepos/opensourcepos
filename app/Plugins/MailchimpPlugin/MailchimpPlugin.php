<?php

namespace App\Plugins\MailchimpPlugin;

use App\Libraries\Plugins\BasePlugin;
use App\Plugins\MailchimpPlugin\Enums\SubscriptionStatus;
use App\Plugins\MailchimpPlugin\Libraries\MailchimpLibrary;
use CodeIgniter\Events\Events;
use Config\Services;
use stdClass;

/**
 * Plugin that integrates OSPOS with Mailchimp for customer newsletter subscriptions.
 * Copyright (C) 2026 opensourcepos.org
 */
class MailchimpPlugin extends BasePlugin
{
    private MailchimpLibrary $mailchimpLibrary;

    public function __construct()
    {
        parent::__construct();

        $apiKey = $this->decryptApiKey($this->getSetting('api_key', ''));
        $this->mailchimpLibrary = new MailchimpLibrary([
            'api_key'      => $apiKey,
            'list_id'      => $this->getSetting('list_id', ''),
            'sync_on_save' => $this->getSetting('sync_on_save', '1'),
        ]);
        log_message('debug', 'MailchimpPlugin initialized');
    }

    public function getPluginId(): string
    {
        return 'mailchimp';
    }

    public function getPluginName(): string
    {
        return 'Mailchimp';
    }

    public function getPluginDescription(): string
    {
        return lang('MailchimpPlugin.description');
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function registerEvents(): void
    {
        Events::on('customer_saved', [$this, 'onCustomerSaved']);
        Events::on('customer_deleted', [$this, 'onCustomerDeleted']);
        Events::on('view:customer_tab_nav', [$this, 'injectMailchimpTabNav']);
        Events::on('view:customer_tab_panels', [$this, 'injectMailchimpTabPanel']);

        log_message('debug', 'Mailchimp plugin events registered');
    }

    public function install(): bool
    {
        log_message('info', 'Installing Mailchimp plugin');

        $this->setSetting('api_key', '');
        $this->setSetting('list_id', '');
        $this->setSetting('sync_on_save', '1');

        return true;
    }

    public function uninstall(): bool
    {
        log_message('info', 'Uninstalling Mailchimp plugin');

        return true;
    }

    public function getConfigView(): ?string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'config';
    }

    public function getSettings(): array
    {
        $apiKey = $this->decryptApiKey($this->getSetting('api_key', ''));

        return [
            'api_key'      => $apiKey,
            'list_id'      => $this->getSetting('list_id', ''),
            'lists'        => $this->getFormattedLists($apiKey),
            'sync_on_save' => $this->getSetting('sync_on_save', '1'),
            'enabled'      => $this->getSetting('enabled', '0'),
        ];
    }

    private function decryptApiKey(string $encryptedKey): string
    {
        if (empty($encryptedKey)) {
            return '';
        }
        try {
            $decoded = base64_decode($encryptedKey, true);
            if ($decoded !== false) {
                return Services::encrypter()->decrypt($decoded);
            }
        } catch (\Exception) {
            // Legacy plaintext or old binary-encrypted key — fall through
        }
        return $encryptedKey;
    }

    private function getFormattedLists(string $apiKey): array
    {
        if (empty($apiKey)) {
            return [];
        }

        $tempLibrary = new MailchimpLibrary(['api_key' => $apiKey]);
        $result = [];

        $lists = $tempLibrary->getLists();
        if ($lists !== false && is_array($lists) && !empty($lists['lists'])) {
            foreach ($lists['lists'] as $list) {
                $result[$list['id']] = $list['name'] . ' [' . $list['stats']['member_count'] . ']';
            }
        }

        return $result;
    }

    public function saveSettings(array $settings): bool
    {
        $normalized = [];

        if (array_key_exists('api_key', $settings)) {
            $rawKey = (string)$settings['api_key'];
            $normalized['api_key'] = !empty($rawKey)
                ? base64_encode(Services::encrypter()->encrypt($rawKey))
                : '';
        }

        if (array_key_exists('list_id', $settings)) {
            $normalized['list_id'] = (string)$settings['list_id'];
        }

        // Clear list_id if api_key is empty
        if (isset($normalized['api_key']) && empty($normalized['api_key'])) {
            $normalized['list_id'] = '';
        }

        if (array_key_exists('sync_on_save', $settings)) {
            $normalized['sync_on_save'] = !empty($settings['sync_on_save']) ? '1' : '0';
        }

        return parent::saveSettings($normalized);
    }

    public function injectMailchimpTabNav(array $data): void
    {
        echo $this->renderView('customer_tab_nav', []);
    }

    public function injectMailchimpTabPanel(array $data): void
    {
        $customerData = $data['customer'] ?? new stdClass();
        $mailchimpData = $this->mailchimpLibrary->getMailchimpViewData($customerData);

        $mailchimpInfo = $mailchimpData['mailchimpActivity'] ?? [];
        $viewData = [
            'mailchimpData' => [
                'status'        => SubscriptionStatus::fromApiString($mailchimpInfo['status'] ?? '')?->value ?? 0,
                'vip'           => $mailchimpInfo['vip'] ?? 0,
                'member_rating' => $mailchimpInfo['member_rating'] ?? 0,
                'email_client'  => $mailchimpInfo['email_client'] ?? '',
            ],
            'mailchimpActivity' => [
                'total'     => $mailchimpInfo['total'] ?? 0,
                'last_open' => $mailchimpInfo['last_open'] ?? '',
                'open'      => $mailchimpInfo['open'] ?? 0,
                'click'     => $mailchimpInfo['click'] ?? 0,
                'unopen'    => $mailchimpInfo['unopen'] ?? 0,
            ],
            'subscriptionStatusOptions' => $mailchimpData['subscriptionStatusOptions'] ?? [],
        ];

        echo $this->renderView('customer_tab', $viewData);
    }

    public function onCustomerSaved(array $personData, array $customerData, ?array $postData = null): void
    {
        if (!$this->shouldSyncOnSave()) {
            return;
        }

        log_message('debug', "Customer saved event received for ID: {$customerData['person_id']}");

        $statusInt = !empty($customerData['consent'])
            ? (int)(($postData ?? [])['status'] ?? SubscriptionStatus::SUBSCRIBED->value)
            : SubscriptionStatus::UNSUBSCRIBED->value;

        $statusEnum = SubscriptionStatus::tryFrom($statusInt) ?? SubscriptionStatus::SUBSCRIBED;
        $subscriptionStatus = strtolower($statusEnum->name);

        $vip = isset($postData['vip']) && (bool)$postData['vip'];

        $this->mailchimpLibrary->synchronizeSubscription($personData, $customerData, $subscriptionStatus, $vip);
    }

    public function onCustomerDeleted(stdClass $customer): void
    {
        log_message('debug', "Customer_deleted event received for ID: {$customer->person_id}");

        $result = $this->mailchimpLibrary->deleteSubscription($customer);

        if ($result === true) {
            log_message('info', "MailchimpPlugin: deleted subscription for customer ID: {$customer->person_id}");
        } elseif (is_array($result)) {
            log_message('error', "MailchimpPlugin: deleteSubscription API error for customer ID: {$customer->person_id} — {$result['title']} (HTTP {$result['status']}): {$result['detail']}");
        } else {
            log_message('error', "MailchimpPlugin: deleteSubscription transport failure for customer ID: {$customer->person_id}");
        }
    }

    private function shouldSyncOnSave(): bool
    {
        return $this->getSetting('sync_on_save', '1') === '1';
    }
}

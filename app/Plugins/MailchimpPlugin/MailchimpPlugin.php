<?php

namespace App\Plugins\MailchimpPlugin;

use App\Libraries\Plugins\BasePlugin;
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

        $this->mailchimpLibrary = new MailchimpLibrary($this->getSettings());
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
        Events::on('view:customer_tabs', [$this, 'injectMailchimpCustomerTab']);

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
        $encryptedKey = $this->getSetting('api_key', '');
        $apiKey = '';

        if (!empty($encryptedKey)) {
            try {
                $apiKey = Services::encrypter()->decrypt($encryptedKey);
            } catch (\Exception $e) {
                // Key stored as plaintext (pre-encryption migration) — use as-is
                $apiKey = $encryptedKey;
            }
        }

        return [
            'api_key'      => $apiKey,
            'list_id'      => $this->getSetting('list_id', ''),
            'lists'        => $this->getFormattedLists($apiKey),
            'sync_on_save' => $this->getSetting('sync_on_save', '1'),
            'enabled'      => $this->getSetting('enabled', '0'),
        ];
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
                ? Services::encrypter()->encrypt($rawKey)
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

    public function injectMailchimpCustomerTab(array $customerData): string
    {
        $mailchimpData = $this->mailchimpLibrary->getMailchimpViewData($customerData);

        return view('Plugins/MailchimpPlugin/Views/customer_tab', $mailchimpData);
    }

    public function onCustomerSaved(array $customerData): void
    {
        if (!$this->shouldSyncOnSave()) {
            return;
        }

        log_message('debug', "Customer saved event received for ID: {$customerData['person_id']}");

        $this->mailchimpLibrary->synchronizeSubscription($customerData);
    }

    public function onCustomerDeleted(stdClass $customer): void
    {
        log_message('debug', "Customer_deleted event received for ID: {$customer->person_id}");

        $this->mailchimpLibrary->deleteSubscription($customer);
    }

    private function shouldSyncOnSave(): bool
    {
        return $this->getSetting('sync_on_save', '1') === '1';
    }


    public function testConnection(): array
    {
        $apiKey = $this->getSetting('api_key');

        if (empty($apiKey)) {
            return ['success' => false, 'message' => lang('api_key_required')];
        }

        $result = $this->mailchimpLibrary->getLists();

        if ($result && isset($result['lists'])) {
            return [
                'success' => true,
                'message' => lang('key_successfully'),
                'lists' => $result['lists']
            ];
        }

        return ['success' => false, 'message' => lang('key_unsuccessfully')];
    }

}

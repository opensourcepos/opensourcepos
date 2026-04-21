<?php

namespace App\Plugins\MailchimpPlugin;

use App\Libraries\Plugins\BasePlugin;
use App\Plugins\MailchimpPlugin\Libraries\MailchimpLibrary;
use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\ResponseInterface;
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
        return $this->lang('mailchimp_description');
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
        $this->setSetting('enabled', '0');

        return true;
    }

    public function uninstall(): bool
    {
        log_message('info', 'Uninstalling Mailchimp plugin');
        return true;
    }

    public function getConfigView(): ?string
    {
        return 'Plugins/MailchimpPlugin/Views/config';
    }

    public function getSettings(): array
    {
        return [
            'api_key' => $this->getSetting('api_key', ''),
            'list_id' => $this->getSetting('list_id', ''),
            'sync_on_save' => $this->getSetting('sync_on_save', '1'),
            'enabled' => $this->getSetting('enabled', '0'),
        ];
    }

    public function saveSettings(array $settings): bool
    {
        $normalized = [];

        if (array_key_exists('api_key', $settings)) {
            $normalized['api_key'] = (string)$settings['api_key'];
        }

        if (array_key_exists('list_id', $settings)) {
            $normalized['list_id'] = (string)$settings['list_id'];
        }

        if (array_key_exists('sync_on_save', $settings)) {
            $normalized['sync_on_save'] = !empty($settings['sync_on_save']) ? '1' : '0';
        }

        return parent::saveSettings($normalized);
    }

    public function injectMailchimpCustomerTab(array $customerData): string
    {
        $mailchimpData = $this->mailchimpLibrary->getMailchimpData($customerData);

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

        $this->mailchimpLibrary->deleteSubscription();
    }

    private function shouldSyncOnSave(): bool
    {
        return $this->getSetting('sync_on_save', '1') === '1';
    }


    public function testConnection(): array
    {
        $apiKey = $this->getSetting('api_key');

        if (empty($apiKey)) {
            return ['success' => false, 'message' => $this->lang('mailchimp_api_key_required')];
        }

        $result = $this->mailchimpLibrary->getLists();

        if ($result && isset($result['lists'])) {
            return [
                'success' => true,
                'message' => $this->lang('mailchimp_key_successfully'),
                'lists' => $result['lists']
            ];
        }

        return ['success' => false, 'message' => $this->lang('mailchimp_key_unsuccessfully')];
    }

    protected function lang(string $key, array $data = []): string  //TODO: The implementation of this is different from the implementation of lang in the framework. We need to make sure it works properly. Primarily it needs to be pulling the strings from the proper place.
    {
        $language = Services::language();
        return $language->getLine($key, $data);
    }

    protected function getPluginDir(): string
    {
        return 'MailchimpPlugin';
    }
}

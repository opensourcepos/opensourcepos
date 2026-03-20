<?php

namespace App\Plugins;

use App\Libraries\Plugins\BasePlugin;
use App\Libraries\Mailchimp_lib;
use CodeIgniter\Events\Events;

/**
 * Plugin that integrates OSPOS with Mailchimp for customer newsletter subscriptions.
 */
class MailchimpPlugin extends BasePlugin
{
    private ?Mailchimp_lib $mailchimpLib = null;

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
        return 'Plugins/mailchimp/config';
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
        if (isset($settings['api_key'])) {
            $this->setSetting('api_key', $settings['api_key']);
        }
        
        if (isset($settings['list_id'])) {
            $this->setSetting('list_id', $settings['list_id']);
        }
        
        if (isset($settings['sync_on_save'])) {
            $this->setSetting('sync_on_save', $settings['sync_on_save'] ? '1' : '0');
        }
        
        return true;
    }

    public function onCustomerSaved(array $customerData): void
    {
        if (!$this->isEnabled() || !$this->shouldSyncOnSave()) {
            return;
        }

        log_message('debug', "Customer saved event received for ID: {$customerData['person_id']}");

        try {
            $this->subscribeCustomer($customerData);
        } catch (\Exception $e) {
            log_message('error', "Failed to sync customer to Mailchimp: {$e->getMessage()}");
        }
    }

    public function onCustomerDeleted(int $customerId): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        log_message('debug', "Customer deleted event received for ID: {$customerId}");
    }

    private function subscribeCustomer(array $customerData): bool
    {
        $apiKey = $this->getSetting('api_key');
        $listId = $this->getSetting('list_id');

        if (empty($apiKey) || empty($listId)) {
            log_message('warning', 'Mailchimp API key or List ID not configured');
            return false;
        }

        if (empty($customerData['email'])) {
            log_message('debug', 'Customer has no email, skipping Mailchimp sync');
            return false;
        }

        $mailchimp = $this->getMailchimpLib(['api_key' => $apiKey]);
        
        $result = $mailchimp->addOrUpdateMember(
            $listId,
            $customerData['email'],
            $customerData['first_name'] ?? '',
            $customerData['last_name'] ?? '',
            'subscribed'
        );

        if ($result) {
            log_message('info', "Successfully subscribed customer {$customerData['email']} to Mailchimp");
            return true;
        }

        return false;
    }

    private function shouldSyncOnSave(): bool
    {
        return $this->getSetting('sync_on_save', '1') === '1';
    }

    private function getMailchimpLib(array $params = []): Mailchimp_lib
    {
        if ($this->mailchimpLib === null) {
            $this->mailchimpLib = new Mailchimp_lib($params);
        }
        return $this->mailchimpLib;
    }

    public function testConnection(): array
    {
        $apiKey = $this->getSetting('api_key');
        
        if (empty($apiKey)) {
            return ['success' => false, 'message' => $this->lang('mailchimp_api_key_required')];
        }

        $mailchimp = $this->getMailchimpLib(['api_key' => $apiKey]);
        $result = $mailchimp->getLists();

        if ($result && isset($result['lists'])) {
            return [
                'success' => true, 
                'message' => $this->lang('mailchimp_key_successfully'),
                'lists' => $result['lists']
            ];
        }

        return ['success' => false, 'message' => $this->lang('mailchimp_key_unsuccessfully')];
    }

    protected function lang(string $key, array $data = []): string
    {
        $language = \Config\Services::language();
        $language->addLanguagePath(APPPATH . 'Plugins/MailchimpPlugin/Language/');
        return $language->getLine($key, $data);
    }

    protected function getPluginDir(): string
    {
        return 'MailchimpPlugin';
    }
}
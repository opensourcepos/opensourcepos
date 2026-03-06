<?php

namespace App\Plugins;

use App\Libraries\Plugins\BasePlugin;
use App\Libraries\Mailchimp_lib;
use CodeIgniter\Events\Events;

/**
 * Mailchimp Integration Plugin
 * 
 * Example plugin that integrates OSPOS with Mailchimp for customer newsletter subscriptions.
 * This demonstrates the plugin system architecture.
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
        return 'Mailchimp Integration';
    }

    public function getPluginDescription(): string
    {
        return 'Integrate with Mailchimp to sync customers to mailing lists when they are created or updated.';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function registerEvents(): void
    {
        Events::on('customer_saved', [$this, 'onCustomerSaved']);
        Events::on('customer_deleted', [$this, 'onCustomerDeleted']);
        
        $this->log('debug', 'Mailchimp plugin events registered');
    }

    public function install(): bool
    {
        $this->log('info', 'Installing Mailchimp plugin');
        
        $this->setSetting('api_key', '');
        $this->setSetting('list_id', '');
        $this->setSetting('sync_on_save', '1');
        $this->setSetting('enabled', '0');
        
        return true;
    }

    public function uninstall(): bool
    {
        $this->log('info', 'Uninstalling Mailchimp plugin');
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

    /**
     * Handle customer saved event.
     * 
     * @param array $customerData Customer information
     */
    public function onCustomerSaved(array $customerData): void
    {
        if (!$this->isEnabled() || !$this->shouldSyncOnSave()) {
            return;
        }

        $this->log('debug', "Customer saved event received for ID: {$customerData['person_id']}");

        try {
            $this->subscribeCustomer($customerData);
        } catch (\Exception $e) {
            $this->log('error', "Failed to sync customer to Mailchimp: {$e->getMessage()}");
        }
    }

    /**
     * Handle customer deleted event.
     * 
     * @param int $customerId Customer ID
     */
    public function onCustomerDeleted(int $customerId): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $this->log('debug', "Customer deleted event received for ID: {$customerId}");
    }

    /**
     * Subscribe customer to Mailchimp list.
     */
    private function subscribeCustomer(array $customerData): bool
    {
        $apiKey = $this->getSetting('api_key');
        $listId = $this->getSetting('list_id');

        if (empty($apiKey) || empty($listId)) {
            $this->log('warning', 'Mailchimp API key or List ID not configured');
            return false;
        }

        if (empty($customerData['email'])) {
            $this->log('debug', 'Customer has no email, skipping Mailchimp sync');
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
            $this->log('info', "Successfully subscribed customer {$customerData['email']} to Mailchimp");
            return true;
        }

        return false;
    }

    /**
     * Check if sync on save is enabled.
     */
    private function shouldSyncOnSave(): bool
    {
        return $this->getSetting('sync_on_save', '1') === '1';
    }

    /**
     * Get Mailchimp library instance.
     */
    private function getMailchimpLib(array $params = []): Mailchimp_lib
    {
        if ($this->mailchimpLib === null) {
            $this->mailchimpLib = new Mailchimp_lib($params);
        }
        return $this->mailchimpLib;
    }

    /**
     * Test the Mailchimp API connection.
     */
    public function testConnection(): array
    {
        $apiKey = $this->getSetting('api_key');
        
        if (empty($apiKey)) {
            return ['success' => false, 'message' => 'API key not configured'];
        }

        $mailchimp = $this->getMailchimpLib(['api_key' => $apiKey]);
        $result = $mailchimp->getLists();

        if ($result && isset($result['lists'])) {
            return [
                'success' => true, 
                'message' => 'API key is valid',
                'lists' => $result['lists']
            ];
        }

        return ['success' => false, 'message' => 'API key is invalid'];
    }
}
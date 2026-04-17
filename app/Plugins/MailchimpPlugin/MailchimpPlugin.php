<?php

namespace App\Plugins\MailchimpPlugin;

use App\Libraries\Plugins\BasePlugin;
use App\Models\Customer;
use App\Plugins\MailchimpPlugin\Libraries\MailchimpLibrary;
use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Exception;
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

        $this->mailchimpLibrary = new MailchimpLibrary();
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
        Events::on('customer_loaded', [$this, 'onCustomerLoaded']);
        Events::on('customer_saved', [$this, 'onCustomerSaved']);
        Events::on('customer_deleted', [$this, 'onCustomerDeleted']);
        Events::on('view:customer_tabs', [$this, 'injectMailchimpCustomerData']);

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

    public function injectMailchimpCustomerData(array $data): string
    {
        return view()
    }

    public function onCustomerLoaded(object $customerInfo): void
    {//TODO: This likely needs to be refactored to a controller function, called here then below it call another function to generate the view data so the mailchimpCustomerForm.php view can be displayed as a partial view.  Does the view need to be called here?
        if (!empty($customerInfo->email)) {
            $listId = $this->getSetting('list_id');
            $mailchimpInfo = $this->mailchimpLibrary->getMemberInfo($listId, $customerInfo->email);
            if ($mailchimpInfo !== false) {
                $customerData['mailchimp_info'] = $mailchimpInfo;

                $customerActivities = $this->mailchimpLibrary->getMemberActivity($listId, $customerInfo->email);
                if ($customerActivities !== false) {
                    if (array_key_exists('activity', $customerActivities)) {
                        $open = 0;
                        $unopen = 0;
                        $click = 0;
                        $total = 0;
                        $lastOpen = '';

                        foreach ($customerActivities['activity'] as $activity) {
                            if ($activity['action'] == 'sent') {
                                ++$unopen;
                            } elseif ($activity['action'] == 'open') {
                                if (empty($lastOpen)) {
                                    $lastOpen = substr($activity['timestamp'], 0, 10);
                                }
                                ++$open;
                            } elseif ($activity['action'] == 'click') {
                                if (empty($lastOpen)) {
                                    $lastOpen = substr($activity['timestamp'], 0, 10);
                                }
                                ++$click;
                            }

                            ++$total;
                        }

                        $customerData['mailchimp_activity']['total'] = $total;
                        $customerData['mailchimp_activity']['open'] = $open;
                        $customerData['mailchimp_activity']['unopen'] = $unopen;
                        $customerData['mailchimp_activity']['click'] = $click;
                        $customerData['mailchimp_activity']['lastopen'] = $lastOpen;
                    }
                }
            }
        }
    }

    public function onCustomerSaved(array $customerData): void
    {
        if (!$this->shouldSyncOnSave()) {
            return;
        }

        log_message('debug', "Customer saved event received for ID: {$customerData['person_id']}");

        try {
            if (!$this->subscribeCustomer($customerData)) {
                throw new Exception("Customer ID {$customerData['person_id']}");
            }
        } catch (Exception $e) {
            log_message('error', "Failed to sync customer to Mailchimp: {$e->getMessage()}");
        }

        //TODO: This is the original code from the Customers->postSave() function. It needs to be handled correctly
        $mailchimpStatus = $this->request->getPost('mailchimp_status'); //TODO: Originally this was a dropdown in the view but needs to be modeled as a static class enum in the plugin and the ID needs to be stored as a column in the mailchimp table along with the customerId
        $listId = $this->getSetting('list_id');
        $this->mailchimpLibrary->addOrUpdateMember(
            $listId,
            $customerData['email'],
            $customerData['first_name'],
            $customerData['last_name'],
            $mailchimpStatus == null ? '' : $mailchimpStatus
        );

        //TODO: this is the code as it looks in the customer CSV import function.
        $this->mailchimpLibrary->addOrUpdateMember(
            $listId,
            $customerData['email'],
            $customerData['first_name'],
            $customerData['last_name'],
            ''
        );
    }

    public function onCustomerDeleted(stdClass $customer): void
    {
        log_message('debug', "Customer_deleted event received for ID: {$customer->person_id}");

        //TODO: This is code from the Customers Controller.  It needs to be adapted
        // remove customer from Mailchimp selected list
        $listId = $this->getSetting('list_id');
        $this->mailchimpLibrary->removeMember($listId, $customer->email);
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
            log_message('info', "Successfully subscribed customer ID {$customerData['person_id']} to Mailchimp");
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
        if ($this->mailchimpLibrary === null) {
            $this->mailchimpLibrary = new Mailchimp_lib($params);
        }
        return $this->mailchimpLibrary;
    }

    /**
     * Gets Mailchimp lists when a valid API key is inserted. Used in app/Views/configs/plugins_config.php
     *
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function postCheckMailchimpApiKey(): ResponseInterface
    {
        $lists = $this->_mailchimp($this->request->getPost('mailchimp_api_key'));
        $success = count($lists) > 0;

        return $this->response->setJSON([
            'success'         => $success,
            'message'         => lang('Config.mailchimp_key_' . ($success ? '' : 'un') . 'successfully'),
            'mailchimp_lists' => $lists
        ]);
    }

    /**
     * This function fetches all the available lists from Mailchimp for the given API key
     */
    private function _mailchimp(string $api_key = ''): array    // TODO: Hungarian notation
    {
        $mailchimp_lib = new Mailchimp_lib(['api_key' => $api_key]);

        $result = [];

        $lists = $mailchimp_lib->getLists();
        if ($lists !== false) {
            if (is_array($lists) && !empty($lists['lists']) && is_array($lists['lists'])) {
                foreach ($lists['lists'] as $list) {
                    $result[$list['id']] = $list['name'] . ' [' . $list['stats']['member_count'] . ']';
                }
            }
        }

        return $result;
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
        $language = Services::language();
        return $language->getLine($key, $data);
    }

    protected function getPluginDir(): string
    {
        return 'MailchimpPlugin';
    }
}

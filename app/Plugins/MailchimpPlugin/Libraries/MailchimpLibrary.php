<?php

namespace app\Plugins\MailchimpPlugin\Libraries;

use App\Plugins\MailchimpPlugin\Enums\SubscriptionStatus;
use App\Plugins\MailchimpPlugin\Models\SubscriptionModel;
use app\Plugins\MailichimpPlugin\Libraries\MailchimpConnector;
use Exception;
use stdClass;

/**
 * Mailchimp library, usable from CI code
 *
 * Library with utility queries to interface Mailchimp v3 API
 *
 * Inspired by the work of ThinkShout: https://github.com/thinkshout/mailchimp-api-php
 */

class MailchimpLibrary
{
    private MailchimpConnector $connector;
    private SubscriptionModel $subscriptionModel;
    private array $settings;

    public function __construct(array $settings = [])
    {
        $apiKey = $settings['api_key'] ?? '';
        $this->connector = new MailchimpConnector($apiKey);
        $this->subscriptionModel = new SubscriptionModel();

        $this->settings = $settings;
    }

    public function synchronizeSubscription(array $customerData): bool
    {
        try {
            if (!$this->subscribeCustomer($customerData)) {
                throw new Exception("Customer ID {$customerData['person_id']}");
            }
        } catch (Exception $e) {
            log_message('error', "Failed to sync customer to Mailchimp: {$e->getMessage()}");
        }

        //call exists
        //if exists get current subscription
        //TODO: This is the original code from the Customers->postSave() function. It needs to be handled correctly
        $mailchimpStatus = $this->subscriptionModel->find($customerData['customer_id']); //TODO: Originally this was a dropdown in the view but needs to be modeled as a static class enum in the plugin and the ID needs to be stored as a column in the mailchimp table along with the customerId
        $this->addOrUpdateMember(
            $this->settings['list_id'],
            $customerData['email'],
            $customerData['first_name'],
            $customerData['last_name'],
            $mailchimpStatus == null ? '' : $mailchimpStatus
        );

        return false;
    }

    private function subscribeCustomer(array $customerData): bool
    {
        $apiKey = $this->settings['api_key'];
        $listId = $this->settings['list_id'];

        if (empty($apiKey) || empty($listId)) {
            log_message('warning', 'Mailchimp API key or List ID not configured');
            return false;
        }

        if (empty($customerData['email'])) {
            log_message('debug', 'Customer has no email, skipping Mailchimp sync');
            return false;
        }

        $result = $this->addOrUpdateMember(
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

    public function deleteSubscription(stdClass $customer): bool
    {
        $this->subscriptionModel->delete($customer->customer_id);

        $listId = $this->settings['list_id'];
        $this->removeMember($listId, $customer->email);

        return false;
    }

    public function getMailchimpData(array $customerData): array
    {
        if (!empty($customerData->email)) {
            $listId = $this->settings['list_id'];
            $mailchimpInfo = $this->getMemberInfo($listId, $customerData->email);

            if ($mailchimpInfo !== false) {
                $mailchimpData['mailchimp_info'] = $mailchimpInfo;

                $mailchimpData['subscriptionStatusOptions'] = $this->getSubscriptionStatusOptionViewData();

                $customerActivities = $this->getMemberActivity($listId, $customerData->email);
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

                        $mailchimpData['mailchimp_activity']['total'] = $total;
                        $mailchimpData['mailchimp_activity']['open'] = $open;
                        $mailchimpData['mailchimp_activity']['unopen'] = $unopen;
                        $mailchimpData['mailchimp_activity']['click'] = $click;
                        $mailchimpData['mailchimp_activity']['lastopen'] = $lastOpen;
                    }
                }

                return $mailchimpData;
            }
        }

        return [];
    }

    private function getSubscriptionStatusOptionViewData(): array
    {
        $statusOptions = [];
        foreach (SubscriptionStatus::cases() as $case) {
            $lowercaseName = strtolower($case->name);
            $statusOptions[(int)$case] = lang("MailchimpPlugin.subscription_status_{$lowercaseName}");
        }

        return $statusOptions;
    }

    /**
     * Gets information about all lists owned by the authenticated account.
     *
     * @param array $parameters
     *   Associative array of optional request parameters.
     *   By the default it places a simple query to list name & id and count of members & merge_fields
     *   NOTE: no space between , and next word is allowed. You will not get the filter to work in full but just the first tag
     * @return array|bool
     * @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/#read-get_lists
     */
    public function getLists(array $parameters = ['fields' => 'lists.id,lists.name,lists.stats.member_count,lists.stats.merge_field_count']): bool|array
    {
        return $this->connector->call('/lists', 'GET', $parameters);
    }

    /**
     * Gets a MailChimp list.
     *
     * @param string $listId
     *   The ID of the list.
     * @param array $parameters Associative array of optional request parameters.
     * @return array|bool
     * @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/#read-get_lists_list_id
     */
    public function getList(string $listId, array $parameters = ['fields' => 'id,name,stats.member_count,stats.merge_field_count']): bool|array
    {
        return $this->connector->call("/lists/$listId", 'GET', $parameters);
    }

    /**
     * Gets information about all members of a MailChimp list.
     *
     * @param string $listId The ID of the list.
     * @param int $count
     * @param int $offset
     * @param array $parameters Associative array of optional request parameters.
     * @return array|bool
     * @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#read-get_lists_list_id_members
     */
    public function getMembers(string $listId, int $count, int $offset, array $parameters = ['fields' => 'members.id,members.email_address,members.unique_email_id,members.status,members.merge_fields']): bool|array
    {
        $parameters += [
            'count'  => $count,
            'offset' => $offset
        ];

        return $this->connector->call("/lists/$listId/members", 'GET', $parameters);
    }

    /**
     * Gets information about a member of a MailChimp list.
     *
     * @param string $listId The ID of the list.
     * @param string $md5Id The member's email address md5 hash which is the id.
     * @param array $parameters Associative array of optional request parameters.
     * @return array|bool
     * @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#read-get_lists_list_id_members_subscriber_hash
     */
    public function getMemberInfoById(string $listId, string $md5Id, array $parameters = ['fields' => 'email_address,status,merge_fields']): bool|array
    {
        return $this->connector->call("/lists/$listId/members/$md5Id", 'GET', $parameters);
    }

    /**
     * Gets information about a member of a MailChimp list.
     *
     * @param string $listId The ID of the list.
     * @param string $email The member's email address.
     * @param array $parameters Associative array of optional request parameters.
     * @return array|bool
     * @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#read-get_lists_list_id_members_subscriber_hash
     */
    public function getMemberInfo(string $listId, string $email, array $parameters = []): bool|array
    {
        return $this->connector->call("/lists/$listId/members/" . md5(strtolower($email)), 'GET', $parameters);
    }

    /**
     * Gets activity related to a member of a MailChimp list.
     *
     * @param string $listId The ID of the list.
     * @param string $email The member's email address.
     * @param array $parameters Associative array of optional request parameters.
     * @return array|bool Associative array of results or false.
     * @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/activity/#read-get_lists_list_id_members_subscriber_hash_activity
     */
    public function getMemberActivity(string $listId, string $email, array $parameters = []): bool|array
    {
        return $this->connector->call("/lists/$listId/members/" . md5(strtolower($email)) . '/activity', 'GET', $parameters);
    }

    /**
     * Adds a new member to a MailChimp list.
     *
     * @param string $listId The ID of the list.
     * @param string $email The email address to add.
     * @param string $firstName
     * @param string $lastName
     * @param array $parameters Associative array of optional request parameters.
     * @return array|bool
     * @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#create-post_lists_list_id_members
     */
    public function addMember(string $listId, string $email, string $firstName, string $lastName, array $parameters = []): bool|array
    {
        $parameters += [
            'email_address' => $email,
            'status'        => 'subscribed',
            'merge_fields'  => [
                'FNAME' => $firstName,
                'LNAME' => $lastName
            ]
        ];

        return $this->connector->call("/lists/$listId/members/", 'POST', $parameters);
    }

    /**
     * Removes a member from a MailChimp list.
     *
     * @param string $listId The ID of the list.
     * @param string $email The member's email address.
     * @return array|bool
     * @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#delete-delete_lists_list_id_members_subscriber_hash
     */
    public function removeMember(string $listId, string $email): bool|array
    {
        return $this->connector->call("/lists/$listId/members/" . md5(strtolower($email)), 'DELETE');
    }

    /**
     * Updates a member of a MailChimp list.
     *
     * @param string $listId The ID of the list.
     * @param string $email The member's email address.
     * @param string $firstName
     * @param string $lastName
     * @param array $parameters Associative array of optional request parameters.
     * @return array|bool
     * @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#edit-patch_lists_list_id_members_subscriber_hash
     */
    public function updateMember(string $listId, string $email, string $firstName, string $lastName, array $parameters = []): bool|array
    {
        $parameters += [
            'status'       => 'subscribed',
            'merge_fields' => [
                'FNAME' => $firstName,
                'LNAME' => $lastName
            ]
        ];

        return $this->connector->call("/lists/$listId/members/" . md5(strtolower($email)), 'PATCH', $parameters);
    }

    /**
     * Adds member new or updates an existing member of a MailChimp list.
     *
     * @param string $listId The ID of the list.
     * @param string $email The member's email address.
     * @param string $firstName
     * @param string $lastName
     * @param string $status
     * @param array $parameters Associative array of optional request parameters.
     * @return array|bool
     * @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#edit-put_lists_list_id_members_subscriber_hash
     */
    public function addOrUpdateMember(string $listId, string $email, string $firstName, string $lastName, string $status, array $parameters = []): bool|array
    {
        $parameters += [
            'email_address' => $email,
            'status'        => $status,
            'status_if_new' => 'subscribed',
            'merge_fields'  => [
                'FNAME' => $firstName,
                'LNAME' => $lastName
            ]
        ];

        return $this->connector->call("/lists/$listId/members/" . md5(strtolower($email)), 'PUT', $parameters);
    }
}

<?php

namespace App\Plugins\MailchimpPlugin\Libraries;

use App\Plugins\MailchimpPlugin\Enums\SubscriptionStatus;
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
    private array $settings;

    public function __construct(array $settings = [])
    {
        $apiKey = $settings['api_key'] ?? '';
        $this->connector = new MailchimpConnector($apiKey);

        $this->settings = $settings;
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
     * @param string $listId
     *   The ID of the list.
     * @param int $count
     * @param int $offset
     * @param array $parameters
     *   Associative array of optional request parameters.
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
     * @param string $listId
     *   The ID of the list.
     * @param string $md5id
     *   The member's email address md5 hash which is the id.
     * @param array $parameters
     *   Associative array of optional request parameters.
     * @return array|bool
     * @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#read-get_lists_list_id_members_subscriber_hash
     */
    public function getMemberInfoById(string $listId, string $md5id, array $parameters = ['fields' => 'email_address,status,merge_fields']): bool|array
    {
        return $this->connector->call("/lists/$listId/members/$md5id", 'GET', $parameters);
    }

    /**
     * Gets information about a member of a MailChimp list.
     *
     * @param string $listId
     *   The ID of the list.
     * @param string $email
     *   The member's email address.
     * @param array $parameters
     *   Associative array of optional request parameters.
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
     * @param string $listId
     *   The ID of the list.
     * @param string $email
     *   The email address to add.
     * @param string $firstName
     * @param string $lastName
     * @param array $parameters
     *   Associative array of optional request parameters.
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
     * @param string $listId
     *   The ID of the list.
     * @param string $email
     *   The member's email address.
     * @return true|array|false true on success (HTTP 204), array with API error details on failure (keys: status, title, detail),
     *                          false on curl transport failure.
     * @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#delete-delete_lists_list_id_members_subscriber_hash
     */
    public function removeMember(string $listId, string $email): bool|array
    {
        return $this->connector->call("/lists/$listId/members/" . md5(strtolower($email)), 'DELETE');
    }

    /**
     * Updates a member of a MailChimp list.
     *
     * @param string $listId
     *   The ID of the list.
     * @param string $email
     *   The member's email address.
     * @param string $firstName
     * @param string $lastName
     * @param array $parameters
     *   Associative array of optional request parameters.
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
     * Adds new or update an existing member of a MailChimp list.
     *
     * @param string $listId
     *   The ID of the list.
     * @param string $email
     *   The member's email address.
     * @param string $firstName
     * @param string $lastName
     * @param string $status
     * @param array $parameters
     *   Associative array of optional request parameters.
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

    public function synchronizeSubscription(array $personData, array $customerData, string $subscriptionStatus, bool $vip = false): bool
    {
        try {
            return $this->subscribeCustomer($personData, $customerData, $subscriptionStatus, $vip);
        } catch (Exception $e) {
            log_message('error', "Failed to sync customer to Mailchimp: {$e->getMessage()}");
            return false;
        }
    }

    private function subscribeCustomer(array $personData, array $customerData, string $subscriptionStatus, bool $vip = false): bool
    {
        $apiKey = $this->settings['api_key'];
        $listId = $this->settings['list_id'];

        if (empty($apiKey) || empty($listId)) {
            log_message('warning', 'Mailchimp API key or List ID not configured');
            return false;
        }

        if (empty($personData['email'])) {
            log_message('debug', 'Customer has no email, skipping Mailchimp sync');
            return false;
        }

        $result = $this->addOrUpdateMember(
            $listId,
            $personData['email'],
            $personData['first_name'] ?? '',
            $personData['last_name'] ?? '',
            $subscriptionStatus,
            ['vip' => $vip]
        );

        if (is_array($result) && isset($result['status']) && is_int($result['status']) && $result['status'] >= 400) {
            log_message('error', 'Mailchimp API error syncing customer ID ' . $customerData['person_id'] . ': ' . json_encode($result));
            return false;
        }

        if ($result) {
            log_message('info', "Successfully synced customer ID {$customerData['person_id']} to Mailchimp as '{$subscriptionStatus}'");
            return true;
        }

        return false;
    }

    /**
     * Deletes a customer's Mailchimp subscription.
     *
     * @param stdClass $customer Customer object with an 'email' property.
     * @return true|array|false true on success, array with API error details on API failure, false on transport failure.
     */
    public function deleteSubscription(stdClass $customer): bool|array
    {
        $listId = $this->settings['list_id'];

        $this->addOrUpdateMember($listId, $customer->email, '', '', 'unsubscribed');

        return $this->removeMember($listId, $customer->email);
    }

    public function getMailchimpViewData(stdClass $customerData): array
    {
        if (!empty($customerData->email)) {
            $listId = $this->settings['list_id'];
            $mailchimpInfo = $this->getMemberInfo($listId, $customerData->email);

            if (is_array($mailchimpInfo) && !(isset($mailchimpInfo['status']) && is_int($mailchimpInfo['status']) && $mailchimpInfo['status'] >= 400)) {
                $mailchimpData['mailchimpActivity'] = $mailchimpInfo;

                $mailchimpData['subscriptionStatusOptions'] = $this->getSubscriptionStatusOptionViewData();

                $customerActivities = $this->getMemberActivity($listId, $customerData->email);
                if ($customerActivities !== false) {
                    if (array_key_exists('activity', $customerActivities)) {
                        $sent = 0;
                        $open = 0;
                        $click = 0;
                        $lastOpen = '';

                        foreach ($customerActivities['activity'] as $activity) {
                            if ($activity['action'] === 'sent') {
                                ++$sent;
                            } elseif ($activity['action'] === 'open') {
                                if (empty($lastOpen)) {
                                    $lastOpen = substr($activity['timestamp'], 0, 10);
                                }
                                ++$open;
                            } elseif ($activity['action'] === 'click') {
                                if (empty($lastOpen)) {
                                    $lastOpen = substr($activity['timestamp'], 0, 10);
                                }
                                ++$click;
                            }
                        }

                        $mailchimpData['mailchimpActivity']['total'] = $sent;
                        $mailchimpData['mailchimpActivity']['open'] = $open;
                        $mailchimpData['mailchimpActivity']['unopen'] = max(0, $sent - $open);
                        $mailchimpData['mailchimpActivity']['click'] = $click;
                        $mailchimpData['mailchimpActivity']['last_open'] = $lastOpen;
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
            $statusOptions[$case->value] = lang("MailchimpPlugin.subscription_status_{$lowercaseName}");
        }

        return $statusOptions;
    }
}

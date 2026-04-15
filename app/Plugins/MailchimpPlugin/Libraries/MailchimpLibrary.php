<?php

namespace app\Plugins\MailchimpPlugin\Libraries;

use app\Plugins\MailichimpPlugin\Libraries\MailchimpConnector;

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

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $apiKey = (count($parameters) > 0 && !empty($parameters['api_key'])) ? $parameters['api_key'] : '';
        $this->connector = new MailchimpConnector($apiKey);
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

        return $this->connector->call("/lists/$listId/members/" . md5(strtolower($email)), 'PATCH', $parameters);    // TODO: Hungarian notation
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

        return $this->connector->call("/lists/$listId/members/" . md5(strtolower($email)), 'PUT', $parameters);    // TODO: Hungarian notation
    }
}

<?php

namespace app\Libraries;

use app\Models\Appconfig;
use CodeIgniter\Encryption\EncrypterInterface;
use CodeIgniter\Encryption\Encryption;

/**
 * MailChimp API v3 REST client Connector
 *
 * Interface for communicating with the Mailchimp v3 API
 *
 * Inspired by the work of:
 *   - Rajitha Bandara: https://github.com/rajitha-bandara/ci-mailchimp-v3-rest-client
 *   - Stefan Ashwell: https://github.com/stef686/codeigniter-mailchimp-api-v3
 *
 * @property appconfig $appconfig
 * @property encryption encryption
 * @property encrypterinterface encrypter
 */
class MailchimpConnector
{
	/**
	 * API Key
	 *
	 * @var	string[]
	 */
	private $_api_key = '';	//TODO: Hungarian notation

	/**
	 * API Endpoint
	 *
	 * @var	string[]
	 */
	private $_api_endpoint = 'https://<dc>.api.mailchimp.com/3.0/';	//TODO: Hungarian notation

	/**
	 * Constructor
	 */
	public function __construct(string $api_key = '')
	{
		$this->appconfig = model('Appconfig');
		$this->encryption = new Encryption();

		if(empty($api_key))
		{
			$this->_api_key = $this->encrypter->decrypt($this->appconfig->get('mailchimp_api_key'));	//TODO: Hungarian notation
		}
		else
		{
			$this->_api_key = $api_key;	//TODO: Hungarian notation
		}

		if(!empty($this->_api_key))	//TODO: Hungarian notation
		{
			// Replace <dc> with correct datacenter obtained from the last part of the api key
			$strings = explode('-', $this->_api_key);	//TODO: Hungarian notation
			if(is_array($strings) && !empty($strings[1]))
			{
				$this->_api_endpoint = str_replace('<dc>', $strings[1], $this->_api_endpoint);	//TODO: Hungarian notation
			}
		}
	}

	/**
	 * Call an API method. Every request needs the API key
	 * @param string $httpVerb The HTTP method to be used
	 * @param string $method The API method to call, e.g. 'lists/list'
	 * @param array $args An array of arguments to pass to the method. Will be json-encoded for you.
	 * @return bool Associative array of json decoded API response.
	 */
	public function call($httpVerb = 'POST', $method, $args = []): bool	//TODO: The optional parameters must be last, not first.
	{
		if(!empty($this->_api_key))	//TODO: Hungarian notation
		{
			return $this->_request($httpVerb, $method, $args);	//TODO: Hungarian notation
		}

		return FALSE;
	}

	/**
	 * Builds the request URL based on request type
	 * @param  string $httpVerb The HTTP method to be used
	 * @param  string $method   The API method to be called
	 * @param  array  $args     Assoc array of parameters to be passed
	 * @return string           Request URL
	 */
	private function _build_request_url(string $httpVerb = 'POST', string $method, array $args = []): string	//TODO: Hungarian notation. Also The optional parameters must be last, not first.
	{
		if($httpVerb == 'GET')
		{
			return $this->_api_endpoint . $method . '?' . http_build_query($args);	//TODO: Hungarian notation
		}

		return $this->_api_endpoint . $method;	//TODO: Hungarian notation
	}

	/**
	 * Performs the underlying HTTP request.
	 * @param string $httpVerb The HTTP method to be used
	 * @param string $method The API method to be called
	 * @param array $args Assoc array of parameters to be passed
	 * @return bool Assoc array of decoded result
	 */
	private function _request(string $httpVerb, string $method, array $args = []): bool	//TODO: Hungarian notation
	{
		$result = FALSE;

		if(($ch = curl_init()) !== FALSE)
		{
			curl_setopt($ch, CURLOPT_URL, $this->_build_request_url($httpVerb, $method, $args));
			curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
			curl_setopt($ch, CURLOPT_USERPWD, "user:" . $this->_api_key);
			curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-MCAPI/3.0');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($args));
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $httpVerb);

			$result = curl_exec($ch);

			curl_close($ch);
		}

		return $result ? json_decode($result, TRUE) : FALSE;
	}
}


/**
 * Mailchimp library, usable from CI code
 *
 * Library with utility queries to interface Mailchimp v3 API
 *
 * Inspired by the work of ThinkShout: https://github.com/thinkshout/mailchimp-api-php
 */

class Mailchimp_lib	//TODO: IMO We need to stick to the one class per file principle.
{
	private $_connector;	//TODO: Hungarian notation

	public function __construct(array $params = [])
	{
		$api_key = (count($params) > 0 && !empty($params['api_key'])) ? $params['api_key'] : '';
		$this->_connector = new MailchimpConnector($api_key);
	}

	/**
	* Gets information about all lists owned by the authenticated account.
	*
	* @param array $parameters
	*   Associative array of optional request parameters.
	*   By the default it places a simple query to list name & id and count of members & merge_fields
	*   NOTE: no space between , and next word is allowed. You will not get the filter to work in full but just the first tag
	*
	*
	* @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/#read-get_lists
	*/
	public function getLists(array $parameters = ['fields' => 'lists.id,lists.name,lists.stats.member_count,lists.stats.merge_field_count']): bool	//TODO: This function name does not follow naming conventions.
	{
		return $this->_connector->call('GET', '/lists', $parameters);	//TODO: Hungarian notation
	}

	/**
	* Gets a MailChimp list.
	*
	* @param string $list_id
	*   The ID of the list.
	* @param array $parameters
	*   Associative array of optional request parameters.
	* @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/#read-get_lists_list_id
	*/
	public function getList(string $list_id, array $parameters = ['fields' => 'id,name,stats.member_count,stats.merge_field_count']): bool	//TODO: This function name does not follow naming conventions.
	{
		return $this->_connector->call('GET', '/lists/' . $list_id, $parameters);	//TODO: Hungarian notation
	}

	/**
	* Gets information about all members of a MailChimp list.
	*
	* @param string $list_id
	*   The ID of the list.
	* @param array $parameters
	*   Associative array of optional request parameters.
	*
	*
	* @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#read-get_lists_list_id_members
	*/
	public function getMembers(string $list_id, int $count, int $offset, array $parameters = ['fields' => 'members.id,members.email_address,members.unique_email_id,members.status,members.merge_fields']): bool	//TODO: This function name does not follow naming conventions.
	{
		$parameters += [
			'count' => $count,
			'offset' => $offset
		];

		return $this->_connector->call('GET', '/lists/' . $list_id . '/members', $parameters);	//TODO: Hungarian notation
	}

	/**
	* Gets information about a member of a MailChimp list.
	*
	* @param string $list_id
	*   The ID of the list.
	* @param string $md5id
	*   The member's email address md5 hash which is the id.
	* @param array $parameters
	*   Associative array of optional request parameters.
	*
	*
	* @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#read-get_lists_list_id_members_subscriber_hash
	*/
	public function getMemberInfoById(string $list_id, string $md5id, array $parameters = ['fields' => 'email_address,status,merge_fields']): bool	//TODO: This function name does not follow naming conventions.
	{
		return $this->_connector->call('GET', '/lists/' . $list_id . '/members/' . $md5id, $parameters);	//TODO: Hungarian notation
	}

	/**
	* Gets information about a member of a MailChimp list.
	*
	* @param string $list_id
	*   The ID of the list.
	* @param string $email
	*   The member's email address.
	* @param array $parameters
	*   Associative array of optional request parameters.
	*
	*
	* @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#read-get_lists_list_id_members_subscriber_hash
	*/
	public function getMemberInfo(string $list_id, string $email, array $parameters = []): bool	//TODO: This function name does not follow naming conventions.
	{
		return $this->_connector->call('GET', '/lists/' . $list_id . '/members/' . md5(strtolower($email)), $parameters);
	}

	/**
	* Gets activity related to a member of a MailChimp list.
	*
	* @param string $list_id
	*   The ID of the list.
	* @param string $email
	*   The member's email address.
	* @param array $parameters
	*   Associative array of optional request parameters.
	*
	*
	* @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/activity/#read-get_lists_list_id_members_subscriber_hash_activity
	*/
	public function getMemberActivity(string $list_id, string $email, array $parameters = []): bool	//TODO: This function name does not follow naming conventions.
	{
		return $this->_connector->call('GET', '/lists/' . $list_id . '/members/' . md5(strtolower($email)) . '/activity', $parameters);	//TODO: Hungarian notation
	}

	/**
	* Adds a new member to a MailChimp list.
	*
	* @param string $list_id
	*   The ID of the list.
	* @param string $email
	*   The email address to add.
	* @param array $parameters
	*   Associative array of optional request parameters.
	*
	*
	* @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#create-post_lists_list_id_members
	*/
	public function addMember(string $list_id, string $email, string $first_name, string $last_name, array $parameters = []): bool	//TODO: This function name does not follow naming conventions.
	{
		$parameters += [
			'email_address' => $email,
			'status' => 'subscribed',
			'merge_fields' => [
				'FNAME' => $first_name,
				'LNAME' => $last_name
			]
		];

		return $this->_connector->call('POST', '/lists/' . $list_id . '/members/', $parameters);	//TODO: Hungarian notation
	}

	/**
	* Removes a member from a MailChimp list.
	*
	* @param string $list_id
	*   The ID of the list.
	* @param string $email
	*   The member's email address.
	*
	*
	* @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#delete-delete_lists_list_id_members_subscriber_hash
	*/
	public function removeMember(string $list_id, string $email): bool	//TODO: This function name does not follow naming conventions.
	{
		return $this->_connector->call('DELETE', '/lists/' . $list_id . '/members/' . md5(strtolower($email)));	//TODO: Hungarian notation
	}

	/**
	* Updates a member of a MailChimp list.
	*
	* @param string $list_id
	*   The ID of the list.
	* @param string $email
	*   The member's email address.
	* @param array $parameters
	*   Associative array of optional request parameters.
	*
	* @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#edit-patch_lists_list_id_members_subscriber_hash
	*/
	public function updateMember(string $list_id, string $email, string $first_name, string $last_name, array $parameters = []): bool	//TODO: This function name does not follow naming conventions.
	{
		$parameters += [
			'status' => 'subscribed',
			'merge_fields' => [
				'FNAME' => $first_name,
				'LNAME' => $last_name
			]
		];

		return $this->_connector->call('PATCH', '/lists/' . $list_id . '/members/' . md5(strtolower($email)), $parameters);	//TODO: Hungarian notation
	}

	/**
	* Adds a new or update an existing member of a MailChimp list.
	*
	* @param string $list_id
	*   The ID of the list.
	* @param string $email
	*   The member's email address.
	* @param array $parameters
	*   Associative array of optional request parameters.
	*
	*
	* @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#edit-put_lists_list_id_members_subscriber_hash
	*/
	public function addOrUpdateMember(string $list_id, string $email, string $first_name, string $last_name, string $status, array $parameters = []): bool	//TODO: This function name does not follow naming conventions.
	{
		$parameters += [
			'email_address' => $email,
			'status' => $status,
			'status_if_new' => 'subscribed',
			'merge_fields' => [
				'FNAME' => $first_name,
				'LNAME' => $last_name
			]
		];

		return $this->_connector->call('PUT', '/lists/' . $list_id . '/members/' . md5(strtolower($email)), $parameters);	//TODO: Hungarian notation
	}
}
?>
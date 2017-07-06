<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * MailChimp API v3 REST client Connector
 *
 * Interface for communicating with the Mailchimp v3 API
 *
 * Inspired by the work of:
 *   - Rajitha Bandara: https://github.com/rajitha-bandara/ci-mailchimp-v3-rest-client
 *   - Stefan Ashwell: https://github.com/stef686/codeigniter-mailchimp-api-v3
 */

class MailchimpConnector
{
	/**
	 * API Key
	 *
	 * @var	string[]
	 */
	private $_api_key = '';

	/**
	 * API Endpoint
	 *
	 * @var	string[]
	 */
	private $_api_endpoint = 'https://<dc>.api.mailchimp.com/3.0/';

    /**
     * Constructor
     */
	public function __construct($api_key = '')
	{
		$CI =& get_instance();

		if(empty($api_key))
		{
			$this->_api_key = $CI->encryption->decrypt($CI->Appconfig->get('mailchimp_api_key'));
		}
		else
		{
			$this->_api_key = $api_key;
		}

		if(!empty($this->_api_key))
		{
			// Replace <dc> with correct datacenter obtained from the last part of the api key
			$strings = explode('-', $this->_api_key);
			if(is_array($strings) && !empty($strings[1]))
			{
				$this->_api_endpoint = str_replace('<dc>', $strings[1], $this->_api_endpoint);
			}
		}
	}

    /**
     * Call an API method. Every request needs the API key
     * @param  string $httpVerb The HTTP method to be used
     * @param  string $method   The API method to call, e.g. 'lists/list'
     * @param  array  $args     An array of arguments to pass to the method. Will be json-encoded for you.
     * @return array            Associative array of json decoded API response.
     */
	public function call($httpVerb = 'POST', $method, $args = array())
	{
		if(!empty($this->_api_key))
		{
			return $this->_request($httpVerb, $method, $args);
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
	private function _build_request_url($httpVerb = 'POST', $method, $args = array())
	{
		if($httpVerb == 'GET')
		{
			return $this->_api_endpoint . $method . '?' . http_build_query($args);
		}

		return $this->_api_endpoint . $method;
	}

    /**
     * Performs the underlying HTTP request.
     * @param  string $httpVerb The HTTP method to be used
     * @param  string $method   The API method to be called
     * @param  array  $args     Assoc array of parameters to be passed
     * @return array            Assoc array of decoded result
     */
	private function _request($httpVerb, $method, $args = array())
	{
		$result = FALSE;

		if(($ch = curl_init()) !== FALSE)
		{
			curl_setopt($ch, CURLOPT_URL, $this->_build_request_url($httpVerb, $method, $args));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
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

class Mailchimp_lib
{
	private $_connector;

	public function __construct(array $params = array())
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
	* @return object
	*
	* @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/#read-get_lists
	*/
	public function getLists(array $parameters = array('fields' => 'lists.id,lists.name,lists.stats.member_count,lists.stats.merge_field_count'))
	{
		return $this->_connector->call('GET', '/lists', $parameters);
	}

	/**
	* Gets a MailChimp list.
	*
	* @param string $list_id
	*   The ID of the list.
	* @param array $parameters
	*   Associative array of optional request parameters.
	*
	* @return object
	*
	* @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/#read-get_lists_list_id
	*/
	public function getList($list_id, $parameters = array('fields' => 'id,name,stats.member_count,stats.merge_field_count'))
	{
		return $this->_connector->call('GET', '/lists/' . $list_id, $parameters);
	}

	/**
	* Gets information about all members of a MailChimp list.
	*
	* @param string $list_id
	*   The ID of the list.
	* @param array $parameters
	*   Associative array of optional request parameters.
	*
	* @return object
	*
	* @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#read-get_lists_list_id_members
	*/
	public function getMembers($list_id, $count, $offset, $parameters = array('fields' => 'members.id,members.email_address,members.unique_email_id,members.status,members.merge_fields'))
	{
		$parameters += [
			'count' => $count,
			'offset' => $offset
		];

		return $this->_connector->call('GET', '/lists/' . $list_id . '/members', $parameters);
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
	* @return object
	*
	* @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#read-get_lists_list_id_members_subscriber_hash
	*/
	public function getMemberInfoById($list_id, $md5id, $parameters = array('fields' => 'email_address,status,merge_fields'))
	{
		return $this->_connector->call('GET', '/lists/' . $list_id . '/members/' . $md5id, $parameters);
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
	* @return object
	*
	* @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#read-get_lists_list_id_members_subscriber_hash
	*/
	public function getMemberInfo($list_id, $email, $parameters = array())
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
	* @return object
	*
	* @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/activity/#read-get_lists_list_id_members_subscriber_hash_activity
	*/
	public function getMemberActivity($list_id, $email, $parameters = array())
	{
		return $this->_connector->call('GET', '/lists/' . $list_id . '/members/' . md5(strtolower($email)) . '/activity', $parameters);
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
	* @return object
	*
	* @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#create-post_lists_list_id_members
	*/
	public function addMember($list_id, $email, $first_name, $last_name, $parameters = array())
	{
		$parameters += [
			'email_address' => $email,
			'status' => 'subscribed',
			'merge_fields' => array(
				'FNAME' => $first_name,
				'LNAME' => $last_name
			)
		];

		return $this->_connector->call('POST', '/lists/' . $list_id . '/members/', $parameters);
	}

	/**
	* Removes a member from a MailChimp list.
	*
	* @param string $list_id
	*   The ID of the list.
	* @param string $email
	*   The member's email address.
	*
	* @return object
	*
	* @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#delete-delete_lists_list_id_members_subscriber_hash
	*/
	public function removeMember($list_id, $email)
	{
		return $this->_connector->call('DELETE', '/lists/' . $list_id . '/members/' . md5(strtolower($email)));
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
	* @return object
	*
	* @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#edit-patch_lists_list_id_members_subscriber_hash
	*/
	public function updateMember($list_id, $email, $first_name, $last_name, $parameters = array())
	{
		$parameters += [
			'status' => 'subscribed',
			'merge_fields' => array(
				'FNAME' => $first_name,
				'LNAME' => $last_name
			)
		];

		return $this->_connector->call('PATCH', '/lists/' . $list_id . '/members/' . md5(strtolower($email)), $parameters);
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
	* @return object
	*
	* @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#edit-put_lists_list_id_members_subscriber_hash
	*/
	public function addOrUpdateMember($list_id, $email, $first_name, $last_name, $status, $parameters = array())
	{
		$parameters += [
			'email_address' => $email,
			'status' => $status,
			'status_if_new' => 'subscribed',
			'merge_fields' => array(
				'FNAME' => $first_name,
				'LNAME' => $last_name
			)
		];

		return $this->_connector->call('PUT', '/lists/' . $list_id . '/members/' . md5(strtolower($email)), $parameters);
	}
}

?>

<?php

namespace app\Plugins\MailchimpPlugin\Libraries;

use CodeIgniter\Encryption\EncrypterInterface;
use Config\Services;

/**
 * MailChimp API v3 REST client Connector
 *
 * Interface for communicating with the Mailchimp v3 API
 *
 * Inspired by the work of:
 *   - Rajitha Bandara: https://github.com/rajitha-bandara/ci-mailchimp-v3-rest-client
 *   - Stefan Ashwell: https://github.com/stef686/codeigniter-mailchimp-api-v3
 *
 * @property encrypterinterface encrypter
 */
class MailchimpConnector
{
    private string $apiKey = '';
    private string $apiEndpoint = 'https://<dc>.api.mailchimp.com/3.0/';

    public function __construct(string $apiKey)
    {
        $encrypter = Services::encrypter();
        $mailchimpApiKey = (isset($apiKey) && !empty($apiKey)) ? $apiKey : '';

        if (!empty($mailchimpApiKey)) {
            $this->apiKey = $encrypter->decrypt($mailchimpApiKey);
        }

        if (!empty($this->apiKey)) {
            // Replace <dc> with correct datacenter obtained from the last part of the api key
            $strings = explode('-', $this->apiKey);
            if (is_array($strings) && !empty($strings[1])) {
                $this->apiEndpoint = str_replace('<dc>', $strings[1], $this->apiEndpoint);
            }
        }
    }

    /**
     * Call an API method. Every request needs the API key
     * @param string $httpVerb The HTTP method to be used
     * @param string $method The API method to call, e.g. 'lists/list'
     * @param array $args An array of arguments to pass to the method. Will be json-encoded for you.
     * @return array|bool Associative array of json decoded API response or false on error.
     */
    public function call(string $method, string $httpVerb = 'POST', array $args = []): bool|array
    {
        return !empty($this->apiKey)
            ? $this->request($httpVerb, $method, $args)
            : false;
    }

    /**
     * Builds the request URL based on the request type
     * @param  string $httpVerb The HTTP method to be used
     * @param  string $method   The API method to be called
     * @param  array  $args     Assoc array of parameters to be passed
     * @return string           Request URL
     */
    private function buildRequestUrl(string $method, string $httpVerb = 'POST', array $args = []): string
    {
        return $httpVerb == 'GET'
            ? $this->apiEndpoint . $method . '?' . http_build_query($args)
            : $this->apiEndpoint . $method;
    }

    /**
     * Performs the underlying HTTP request.
     * @param string $httpVerb The HTTP method to be used
     * @param string $method The API method to be called
     * @param array $args Associative array of parameters to be passed
     * @return bool|array Associative array of decoded result or False
     */
    private function request(string $httpVerb, string $method, array $args = []): bool|array
    {
        $result = false;

        if (($ch = curl_init()) !== false) {
            curl_setopt($ch, CURLOPT_URL, $this->buildRequestUrl($method, $httpVerb, $args));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_USERPWD, "user:" . $this->apiKey);
            curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-MCAPI/3.0');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($args));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $httpVerb);

            $result = curl_exec($ch);

            curl_close($ch);
        }

        return $result ? json_decode($result, true) : false;
    }
}

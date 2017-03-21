<?php

namespace Racecore\GATracking;

use Racecore\GATracking\Request;
use Racecore\GATracking\Client;
use Racecore\GATracking\Exception;
use Racecore\GATracking\Tracking;

/**
 * Google Analytics Measurement PHP Class
 * Licensed under the 3-clause BSD License.
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * Google Documentation
 * https://developers.google.com/analytics/devguides/collection/protocol/v1/
 *
 * @author  Marco Rieger
 * @email   Rieger(at)racecore.de
 * @git     https://github.com/ins0
 * @url     http://www.racecore.de
 * @package Racecore\GATracking\Tracking
 */
class GATracking
{
    /** @var null */
    private $analyticsAccountUid = null;

    /** @var array */
    private $options = array(
        'client_create_random_id' => true, // create a random client id when the class can't fetch the current client id or none is provided by "client_id"
        'client_fallback_id' => 555, // fallback client id when cid was not found and random client id is off
        'client_id' => null,    // override client id
        'user_id' => null,  // determine current user id

        // adapter options
        'adapter' => array(
            'async' => true, // requests to google are async - don't wait for google server response
            'ssl' => false // use ssl connection to google server
        )

        // use proxy
        /**
        'proxy' => array(
            'ip' => '127.0.0.1', // override the proxy ip with this one
            'user_agent' => 'override agent' // override the proxy user agent
        ),
        **/
    );

    /** @var Client\AbstractClientAdapter */
    private $clientAdapter = null;

    /** @var string */
    private $apiProtocolVersion = '1';
    private $apiEndpointUrl = 'http://www.google-analytics.com/collect';

    /**
     * @param $analyticsAccountUid
     * @param array $options
     * @param Client\AbstractClientAdapter $clientAdapter
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($analyticsAccountUid, $options = array(), Client\AbstractClientAdapter $clientAdapter = null)
    {
        if (empty($analyticsAccountUid)) {
            throw new Exception\InvalidArgumentException('Google Account/Tracking ID not provided');
        }

        $this->analyticsAccountUid = $analyticsAccountUid;

        if (!class_exists('Racecore\GATracking\Client\Adapter\Socket')) {
            require_once( dirname(__FILE__) . '/Autoloader.php');
            Autoloader::register(dirname(__FILE__).'/../../../src/');
        }

        if (!$clientAdapter) {
            $clientAdapter = new Client\Adapter\Socket();
        }
        $this->setClientAdapter($clientAdapter);

        if (!empty($options)) {
            $this->setOptions(
                array_merge($this->options, $options)
            );
        }
    }

    /**
     * Return Analytics Account ID
     *
     * @return null
     */
    public function getAnalyticsAccountUid()
    {
        return $this->analyticsAccountUid;
    }

    /**
     * Set the Analytics Account ID
     *
     * @param null $analyticsAccountUid
     */
    public function setAnalyticsAccountUid($analyticsAccountUid)
    {
        $this->analyticsAccountUid = $analyticsAccountUid;
    }

    /**
     * Get the current Client Adapter
     *
     * @return Client\AbstractClientAdapter
     */
    public function getClientAdapter()
    {
        return $this->clientAdapter;
    }

    /**
     * Set the current Client Adapter
     *
     * @param Client\AbstractClientAdapter $adapter
     */
    public function setClientAdapter(Client\AbstractClientAdapter $adapter)
    {
        $this->clientAdapter = $adapter;
    }

    /**
     * Set Options
     *
     * @param $options
     * @throws Exception\InvalidArgumentException
     */
    public function setOptions($options)
    {
        if (!is_array($options)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '[%s] expects array; received [%s]',
                __METHOD__,
                gettype($options)
            ));
        }

        $this->options = $options;
    }

    /**
     * Set single Option
     *
     * @param $key
     * @param $value
     */
    public function setOption($key, $value)
    {
        if (isset($this->options[$key]) && is_array($this->options[$key]) && is_array($value)) {
            $oldValues = $this->options[$key];
            $value = array_merge($oldValues, $value);
        }

        $this->options[$key] = $value;
    }

    /**
     * Return Options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Return Single Option
     *
     * @param $key
     * @return null|mixed
     */
    public function getOption($key)
    {
        if (!isset($this->options[$key])) {
            return null;
        }

        return $this->options[$key];
    }

    /**
     * Sets the used clientId
     *
     * @param $clientId
     */
    public function setClientId($clientId)
    {
        $this->setOption('client_id', $clientId);
    }

    /**
     * Return the Current Client Id
     *
     * @return string
     */
    public function getClientId()
    {
        $clientId = $this->getOption('client_id');
        if ($clientId) {
            return $clientId;
        }

        // collect user specific data
        if (isset($_COOKIE['_ga'])) {
            $gaCookie = explode('.', $_COOKIE['_ga']);
            if (isset($gaCookie[2])) {
                // check if uuid
                if ($this->checkUuid($gaCookie[2])) {
                    // uuid set in cookie
                    return $gaCookie[2];
                } elseif (isset($gaCookie[2]) && isset($gaCookie[3])) {
                    // google old client id
                    return $gaCookie[2] . '.' . $gaCookie[3];
                }
            }
        }

        // nothing found - fallback
        $generateClientId = $this->getOption('client_create_random_id');
        if ($generateClientId) {
            return $this->generateUuid();
        }

        return $this->getOption('client_fallback_id');
    }

    /**
     * Check if is a valid UUID v4
     *
     * @param $uuid
     * @return int
     */
    final private function checkUuid($uuid)
    {
        return preg_match(
            '#^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$#i',
            $uuid
        );
    }

    /**
     * Generate UUID v4 function - needed to generate a CID when one isn't available
     *
     * @author Andrew Moore http://www.php.net/manual/en/function.uniqid.php#94959
     * @return string
     */
    final private function generateUuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * Build the Tracking Payload Data
     *
     * @param Tracking\AbstractTracking $event
     * @return array
     * @throws Exception\MissingConfigurationException
     */
    protected function getTrackingPayloadData(Tracking\AbstractTracking $event)
    {
        $payloadData = $event->getPackage();
        $payloadData['v'] = $this->apiProtocolVersion; // protocol version
        $payloadData['tid'] = $this->analyticsAccountUid; // account id
        $payloadData['uid'] = $this->getOption('user_id');
        $payloadData['cid'] = $this->getClientId();

        $proxy = $this->getOption('proxy');
        if ($proxy) {
            if (!isset($proxy['ip'])) {
                throw new Exception\MissingConfigurationException('proxy options need "ip" key/value');
            }

            if (isset($proxy['user_agent'])) {
                $payloadData['ua'] = $proxy['user_agent'];
            }

            $payloadData['uip'] = $proxy['ip'];
        }

        return array_filter($payloadData);
    }

    /**
     * Call the client adapter
     *
     * @param $tracking
     * @throws Exception\InvalidArgumentException
     * @throws Exception\MissingConfigurationException
     * @return Request\TrackingRequestCollection
     */
    private function callEndpoint($tracking)
    {
        $trackingHolder = is_array($tracking) ? $tracking : array($tracking);
        $trackingCollection = new Request\TrackingRequestCollection();

        foreach ($trackingHolder as $tracking) {
            if (!$tracking instanceof Tracking\AbstractTracking) {
                continue;
            }

            $payloadData = $this->getTrackingPayloadData($tracking);

            $trackingRequest = new Request\TrackingRequest($payloadData);
            $trackingCollection->add($trackingRequest);
        }

        $adapterOptions = $this->getOption('adapter');
        $clientAdapter = $this->clientAdapter;
        $clientAdapter->setOptions($adapterOptions);

        return $clientAdapter->send($this->apiEndpointUrl, $trackingCollection);
    }

    /**
     * Create a Tracking Class Instance - eg. "Event" or "Ecommerce\Transaction"
     *
     * @param $className
     * @param null $options
     * @return bool
     */
    public function createTracking($className, $options = null)
    {
        if (strstr(strtolower($className), 'abstracttracking')) {
            return false;
        }

        $class = 'Racecore\GATracking\Tracking\\' . $className;
        if ($options) {
            return new $class($options);
        }
        return new $class;
    }

    /**
     * Send single tracking request
     *
     * @param Tracking\AbstractTracking $tracking
     * @return Tracking\AbstractTracking
     */
    public function sendTracking(Tracking\AbstractTracking $tracking)
    {
        $responseCollection = $this->callEndpoint($tracking);
        $responseCollection->rewind();
        return $responseCollection->current();
    }

    /**
     * Send multiple tracking request
     *
     * @param $array
     * @return Request\TrackingRequestCollection
     */
    public function sendMultipleTracking($array)
    {
        return $this->callEndpoint($array);
    }
}

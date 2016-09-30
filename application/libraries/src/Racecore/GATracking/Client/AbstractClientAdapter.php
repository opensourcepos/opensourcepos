<?php

namespace Racecore\GATracking\Client;

use Racecore\GATracking\Exception;
use Racecore\GATracking\Request;

class AbstractClientAdapter implements ClientAdapterInterface
{
    private $options = array();

    public function __construct($options = array())
    {
        $this->options = $options;
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
     * Return Options
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Return Single Option
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

    public function send($url, Request\TrackingRequestCollection $collection)
    {

    }
}

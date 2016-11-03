<?php

namespace Racecore\GATracking\Request;

use Racecore\GATracking\Tracking;

class TrackingRequest
{
    private $payload = array();
    private $responseHeader = array();

    public function __construct(array $payload = array())
    {
        $this->payload = $payload;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param array $payload
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
    }

    /**
     * @return mixed
     */
    public function getResponseHeader()
    {
        return $this->responseHeader;
    }

    /**
     * @param mixed $responseHeader
     */
    public function setResponseHeader($responseHeader)
    {
        $this->responseHeader = $responseHeader;
    }
}

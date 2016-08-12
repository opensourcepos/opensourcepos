<?php

namespace Racecore\GATracking\Client;

use Racecore\GATracking\Request;

interface ClientAdapterInterface
{
    public function __construct($options = array());
    public function setOptions($options);
    public function send($url, Request\TrackingRequestCollection $collection);
}

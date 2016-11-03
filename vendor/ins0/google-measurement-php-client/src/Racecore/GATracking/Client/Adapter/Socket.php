<?php

namespace Racecore\GATracking\Client\Adapter;

use Racecore\GATracking\Client;
use Racecore\GATracking\Exception;
use Racecore\GATracking\Request;

class Socket extends Client\AbstractClientAdapter
{
    const READ_TIMEOUT = 3;
    const READ_BUFFER = 8192;

    private $connection = null;

    /**
     * Create Connection to the Google Server
     * @param $endpoint
     * @throws Exception\EndpointServerException
     */
    private function createConnection($endpoint)
    {
        // port
        $port = $this->getOption('ssl') == true ? 443 : 80;

        if (!($connection = socket_create(AF_INET, SOCK_STREAM, SOL_TCP))) {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            throw new Exception\EndpointServerException('Analytics Socket failure! Error: ' . $errormsg);
        }

        if (!socket_connect($connection, $endpoint['host'], $port)) {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            throw new Exception\EndpointServerException('Analytics Host not reachable! Error: ' . $errormsg);
        }

        socket_set_option($connection, SOL_SOCKET, SO_RCVTIMEO, array('sec' => self::READ_TIMEOUT, 'usec' => 0));

        if ($this->getOption('async')) {
            socket_set_nonblock($connection);
        }

        $this->connection = $connection;
    }

    /**
     * Write the connection header
     * @param $endpoint
     * @param Request\TrackingRequest $request
     * @param bool $lastData
     * @return string
     * @throws Exception\EndpointServerException
     */
    private function writeHeader($endpoint, Request\TrackingRequest $request, $lastData = false)
    {
        // create data
        $payloadString = http_build_query($request->getPayload());
        $payloadLength = strlen($payloadString);

        $header =   'POST ' . $endpoint['path'] . ' HTTP/1.1' . "\r\n" .
            'Host: ' . $endpoint['host'] . "\r\n" .
            'User-Agent: Google-Measurement-PHP-Client' . "\r\n" .
            'Content-Length: ' . $payloadLength . "\r\n" .
            ($lastData ? 'Connection: Close' . "\r\n" : '') . "\r\n";

        // fwrite + check if fwrite was ok
        if (!socket_write($this->connection, $header) || !socket_write($this->connection, $payloadString)) {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            throw new Exception\EndpointServerException('Server closed connection unexpectedly. Error: ' . $errormsg);
        }

        return $header;
    }

    /**
     * Read from the current connection
     * @param Request\TrackingRequest $request
     * @return array|false
     */
    private function readConnection(Request\TrackingRequest $request)
    {
        // response
        $response = '';

        // receive response
        do {
            $out = @socket_read($this->connection, self::READ_BUFFER);
            $response .= $out;

            if (!$out || strlen($out) < self::READ_BUFFER) {
                break;
            }
        } while (true);

        // response
        $responseContainer = explode("\r\n\r\n", $response, 2);
        return explode("\r\n", $responseContainer[0]);
    }

    /**
     * Send the Request Collection to a Server
     * @param $url
     * @param Request\TrackingRequestCollection $requestCollection
     * @return Request\TrackingRequestCollection|void
     * @throws Exception\EndpointServerException
     */
    public function send($url, Request\TrackingRequestCollection $requestCollection)
    {
        // get endpoint
        $endpoint = parse_url($url);

        $this->createConnection($endpoint);

        /** @var Request\TrackingRequest $request */
        while ($requestCollection->valid()) {
            $request = $requestCollection->current();
            $requestCollection->next();

            $this->writeHeader($endpoint, $request, !$requestCollection->valid());
            $responseHeader = $this->readConnection($request);

            $request->setResponseHeader($responseHeader);
        }
        // connection close
        socket_close($this->connection);

        return $requestCollection;
    }
}

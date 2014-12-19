<?php

namespace Janus\ServiceRegistry\Service;

use Guzzle\Http\Client;
use Guzzle\Http\Exception\RequestException;

class RemotePublisher
{
    /** @var  ConnectionService */
    private $connectionService;

    /** @var  Client */
    private $client;

    /**
     * @var string
     */
    private $remoteEndpoint;

    /**
     * @param ConnectionService $connectionService
     * @param Client $client
     * @param string $remoteEndpoint
     */
    public function __construct(ConnectionService $connectionService, Client $client, $remoteEndpoint)
    {
        $this->connectionService = $connectionService;
        $this->client = $client;
        $this->remoteEndpoint = $remoteEndpoint;
    }

    /**
     * Publishes metadata to external endpoint
     *
     * @return bool, true if successful
     */
    public function publish()
    {
        $connections = $this->connectionService->findWithFilters();
        try {
            $this->client->post($this->remoteEndpoint, null, json_encode($connections))->send();
        } catch(RequestException $ex) {
            // @todo log
            return false;
        }

        return true;
    }
} 
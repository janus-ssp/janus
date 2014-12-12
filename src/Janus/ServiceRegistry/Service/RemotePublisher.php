<?php

namespace Janus\ServiceRegistry\Service;

use Guzzle\Http\Client;

class RemotePublisher
{
    /** @var  ConnectionService */
    private $connectionService;

    /** @var  Client */
    private $client;

    public function __construct(ConnectionService $connectionService, Client $client)
    {
        $this->connectionService = $connectionService;
        $this->client = $client;
    }

    /**
     * Publishes metadata to external endpoint
     */
    public function publish()
    {
        $connections = $this->connectionService->findWithFilters();
        $response = $this->client->post('test');
    }
} 
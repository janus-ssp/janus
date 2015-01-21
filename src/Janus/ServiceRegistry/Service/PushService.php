<?php

namespace Janus\ServiceRegistry\Service;

use Guzzle\Http\Client;
use Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection\ConfigProxy;
use JMS\Serializer\SerializerBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * Service layer for all kinds of connection related logic
 *
 * Class Janus\ServiceRegistry\Service\ConnectionService
 */
class PushService
{
    /**
     * @var ConnectionService
     */
    private $connectionService;

    /**
     * JANUS configuration
     * @var ConfigProxy
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ConnectionService $connectionService
     * @param ConfigProxy $config
     */
    public function __construct(
        ConnectionService $connectionService,
        ConfigProxy $config,
        LoggerInterface $logger
    ) {
        $this->connectionService = $connectionService;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function pushToAll()
    {
        $remoteIds = array_keys($this->config->getArray('push.remote'));

        $responses = array();
        foreach ($remoteIds as $remoteId) {
            $responses[$remoteId] = $this->push($remoteId);
        }
        return $responses;
    }

    public function push($remoteId)
    {
        $remotes = $this->config->getArray('push.remote');
        if (!isset($remotes[$remoteId])) {
            throw new \InvalidArgumentException("Remote 'remote' does not exist");
        }

        $remoteUrl = $remotes[$remoteId]['url'];
        $connections = $this->connectionService->findAll();

        $serializer = SerializerBuilder::create()->build();
        $serializedConnections = $serializer->serialize($connections, 'json');

        $client = new Client();
        $request = $client->createRequest(
            'POST',
            $remoteUrl,
            array(
                'Content-Type'=>'application/json',
                'User-Agent'=> 'JANUS Guzzle HTTP Client (see: https://github.com/janus-ssp/janus)',
            ),
            $serializedConnections,
            $this->config->getArray('push.requestOptions')
        );
        return $request->send()->__toString();
    }
}

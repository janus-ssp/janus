<?php

namespace Janus\ServiceRegistry\Service;

use GuzzleHttp\Client;
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

        $username = parse_url($remoteUrl, PHP_URL_USER);
        $password = parse_url($remoteUrl, PHP_URL_PASS);

        $part = $username . ':' . $password . '@';
        $remoteUrl = str_replace($part, '', $remoteUrl);

        $connections = $this->connectionService->findAll();
        $requestOptions = $this->config->getArray('push.requestOptions');

        $serializer = SerializerBuilder::create()->build();
        $serializedConnections = $serializer->serialize($connections, 'json');

        $client = new Client(
            [
                'base_uri' => ''
            ]
        );

        $response = $client->request(
            'POST',
            $remoteUrl,
            [
                'auth' => [$username, $password],
                'headers' => ['Content-Type' => 'application/json'],
                'body' => $serializedConnections,
                'verify' => isSet($requestOptions['verify']) ? (bool) $requestOptions['verify'] : false
            ]
        );

        return $response->getStatusCode();
    }
}

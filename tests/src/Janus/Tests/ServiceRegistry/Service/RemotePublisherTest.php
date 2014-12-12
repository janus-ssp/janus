<?php
namespace src\Janus\Tests\ServiceRegistry\Service;

use Janus\ServiceRegistry\Service\ConnectionService;
use Phake;
use Guzzle\Http\Client;
use Janus\ServiceRegistry\Service\RemotePublisher;
use PHPUnit_Framework_TestCase;

class RemotePublisherTest extends PHPUnit_Framework_TestCase
{
    public function testPublisherPushesMetadataToHttpClient()
    {
        /** @var ConnectionService $connectionServiceMock */
        $connectionServiceMock = Phake::mock('Janus\ServiceRegistry\Service\ConnectionService');

        /** @var Client $clientMock */
        $clientMock = Phake::mock('Guzzle\Http\Client');

        $publisher = new RemotePublisher($connectionServiceMock, $clientMock);
        $publisher->publish();

        Phake::verify($connectionServiceMock)->findWithFilters();
        Phake::verify($clientMock)->post('test');
    }
}
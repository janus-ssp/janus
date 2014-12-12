<?php
namespace src\Janus\Tests\ServiceRegistry\Service;

use Janus\ServiceRegistry\Connection\ConnectionDto;
use Janus\ServiceRegistry\Connection\ConnectionDtoCollection;
use Janus\ServiceRegistry\Service\ConnectionService;
use Phake;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Request;
use Janus\ServiceRegistry\Service\RemotePublisher;
use PHPUnit_Framework_TestCase;

class RemotePublisherTest extends PHPUnit_Framework_TestCase
{
    public function testPublisherPushesMetadataToHttpClient()
    {
        /** @var ConnectionService $connectionServiceMock */
        $connectionServiceMock = Phake::mock('Janus\ServiceRegistry\Service\ConnectionService');

        // Let connection service return collection
        $connectionDto = new ConnectionDto();
        $connectionDto->id = 1;
        $connectionDtoCollection = new ConnectionDtoCollection(array($connectionDto));
        Phake::when($connectionServiceMock)
            ->findWithFilters()
            ->thenReturn($connectionDtoCollection);

        /** @var Client $clientMock */
        $clientMock = Phake::mock('Guzzle\Http\Client');

        /** @var Request $messageRequestMock */
        $messageRequestMock = Phake::mock('Guzzle\Http\Message\Request');
        Phake::when($clientMock)
            ->post(Phake::anyParameters())
            ->thenReturn($messageRequestMock);

        $publisher = new RemotePublisher($connectionServiceMock, $clientMock, 'http://remote-endpoint');
        $this->assertTrue($publisher->publish());

        Phake::verify($connectionServiceMock)->findWithFilters();
        $expectedPostBody = <<<JSON_BODY
{"connections":{"1":{"id":1,"connection":null,"name":null,"revisionNr":null,"state":null,"type":null,"expirationDate":null,"metadataUrl":null,"metadataValidUntil":null,"metadataCacheUntil":null,"allowAllEntities":null,"arpAttributes":null,"manipulationCode":null,"parentRevisionNr":null,"revisionNote":null,"notes":null,"isActive":null,"updatedByUserName":null,"createdAtDate":null,"updatedAtDate":null,"updatedFromIp":null,"metadata":null,"allowedConnections":[],"blockedConnections":[],"disableConsentConnections":[]}},"offset":null,"limit":null}
JSON_BODY;

        Phake::verify($clientMock)->post('http://remote-endpoint', null, $expectedPostBody);
    }
    // @todo test client error
}
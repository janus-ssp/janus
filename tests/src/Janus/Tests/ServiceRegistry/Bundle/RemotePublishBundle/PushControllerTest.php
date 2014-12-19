<?php

namespace src\Janus\Tests\ServiceRegistry\Bundle\RemotePublishBundle;

use Janus\ServiceRegistry\Bundle\RemotePublishBundle\Controller\PushController;
use Phake;
use PHPUnit_Framework_TestCase;

class PushControllerTest extends PHPUnit_Framework_TestCase
{
    public function testReturnsJsonResponseWithFoundStatusCodeOnSuccess()
    {
        $publisherMock = Phake::mock('Janus\ServiceRegistry\Service\RemotePublisher');
        Phake::when($publisherMock)
            ->publish()
            ->thenReturn(true);
        $controller = new PushController($publisherMock);

        $response = $controller->pushAction();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testReturnsJsonResponseWithServerErrordStatusCodeOnFailure()
    {
        $publisherMock = Phake::mock('Janus\ServiceRegistry\Service\RemotePublisher');
        Phake::when($publisherMock)
            ->publish()
            ->thenReturn(false);
        $controller = new PushController($publisherMock);

        $response = $controller->pushAction();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
        $this->assertEquals(500, $response->getStatusCode());
    }
} 
<?php

namespace Janus\ServiceRegistry\Bundle\RemotePublishBundle\Controller;

use Janus\ServiceRegistry\Entity\Connection\Revision;
use Janus\ServiceRegistry\Service\RemotePublisher;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class PushController extends Controller
{
    /** @var  RemotePublisher */
    private $publisher;

    /**
     * @param RemotePublisher $publisher
     */
    public function __construct(RemotePublisher $publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * @return JsonResponse
     */
    public function pushAction()
    {
        $response = $this->publisher->publish();

        return new JsonResponse();
    }
}
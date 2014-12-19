<?php
namespace Janus\ServiceRegistry\Bundle\RemotePublishBundle\Controller;

use Janus\ServiceRegistry\Service\RemotePublisher;
use Symfony\Component\HttpFoundation\JsonResponse;

class PushController
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
        if ($this->publisher->publish()) {
            return new JsonResponse();
        }

        return new JsonResponse('Data could not be published', 500);
    }
}
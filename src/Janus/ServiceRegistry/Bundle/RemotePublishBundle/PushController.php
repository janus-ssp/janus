<?php

namespace Janus\ServiceRegistry\Bundle\RemotePublishBundle\Controller;

use Janus\ServiceRegistry\Entity\Connection\Revision;
use Janus\ServiceRegistry\Service\ConnectionService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class PushController extends Controller
{
    /** @var  ConnectionService */
    private $connectionService;

    /**
     * @param ConnectionService $connectionService
     */
    public function __construct(ConnectionService $connectionService)
    {
        $this->connectionService = $connectionService;
    }

    /**
     * @return JsonResponse
     */
    public function pushAction()
    {
        // @todo select only changed entities
        $connectionDtoCollection = $this->connectionService->findWithFilters(
            array(),
            null,
            'DESC'
        );

        // @todo send data to remote application instead
        return new JsonResponse($connectionDtoCollection);
    }
}
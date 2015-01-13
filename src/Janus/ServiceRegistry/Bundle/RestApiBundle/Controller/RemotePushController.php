<?php

namespace Janus\ServiceRegistry\Bundle\RestApiBundle\Controller;

use Janus\ServiceRegistry\Entity\Connection;
use Janus\ServiceRegistry\Service\PushService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormTypeInterface;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\RouteRedirectView;

use JMS\SecurityExtraBundle\Annotation\Secure;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Janus\ServiceRegistry\Entity\Connection\Revision;
use Janus\ServiceRegistry\Service\ConnectionService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Rest controller for connections
 *
 * @package Janus\ServiceRegistryBundle\Controller
 */
class RemotePushController extends FOSRestController
{
    public function postRemotePushesAction()
    {
        ini_set('max_execution_time', 300);
        $this->get('janus_logger')->info("Starting push to all for " . $this->getUsername());

        $result = $this->getService()->pushToAll();

        $this->get('janus_logger')->info("Ending push to all for " . $this->getUsername());

        return new Response(json_encode($result));
    }

    /**
     * Creates a new connection from the submitted data.
     *
     * @Secure("Create new Entity")
     */
    public function postRemotePushAction($remoteId)
    {
        ini_set('max_execution_time', 300);
        $this->get('janus_logger')->info("Starting push to $remoteId for " . $this->getUsername());

        $result = $this->getService()->push($remoteId);

        $this->get('janus_logger')->info("Ended push to $remoteId for " . $this->getUsername());

        return new Response(json_encode($result));
    }

    private function getUsername()
    {
        /** @var SecurityContext $securityContext */
        $securityContext = $this->get('security.context');
        return $securityContext->getToken()->getUsername();
    }

    /**
     * @return PushService
     */
    protected function getService()
    {
        return $this->get('push_service');
    }
}

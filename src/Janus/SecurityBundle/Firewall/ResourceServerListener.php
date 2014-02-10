<?php

namespace Janus\SecurityBundle\Firewall;

use Symfony\Bridge\Doctrine\Tests\Form\ChoiceList\AbstractEntityChoiceListSingleStringIdTest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Janus\SecurityBundle\Authentication\Token\ResourceServerToken;
use FOS\RestBundle\Util\Codes;

class ResourceServerListener implements ListenerInterface
{
    protected $securityContext;
    protected $authenticationManager;

    public function __construct(
        SecurityContextInterface $securityContext,
        AuthenticationManagerInterface $authenticationManager
    )
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
    }

    public function handle(GetResponseEvent $event)
    {
        $headers = $this->getHeadersFromEvent($event) ;
        $accessToken = $this->getAccessToken($headers);

        if ($accessToken) {
            try {
                $authToken = $this->authenticationManager->authenticate(new ResourceServerToken($accessToken, array()));
                $this->securityContext->setToken($authToken);
                return;
            } catch (AuthenticationException $failed) {
                // By default deny authorization
            }
        }
        $response = new Response();
        $response->setStatusCode(Codes::HTTP_FORBIDDEN);
        $event->setResponse($response);
    }

    /**
     * Returns headers including AUTHORIZATION
     *
     * @param GetResponseEvent $event
     * @return array
     */
    private function getHeadersFromEvent(GetResponseEvent $event)
    {
        // @todo inject this?
        $headers = $event->getRequest()->headers->all();
        // @todo find what exactly causes this and if a better fix can be created
        // Apache removes AUTHORIZATION HEADER so headers need to be requested directly from the server.
        if (function_exists('apache_request_headers')) {
            $apacheHeaders = apache_request_headers();
            if (isset($apacheHeaders['Authorization'])) {
                $headers['authorization'][] = $apacheHeaders['Authorization'];
            }
        }

        return $headers;
    }

    /**
     * Tries to extract access token from headers
     *
     * @param array $headers
     * @return mixed
     */
    private function getAccessToken(array $headers)
    {
        if (!isset($headers['authorization'])) {
            return;
        }

        $matches = array();
        preg_match('/bearer (.*)/i', $headers['authorization'][0], $matches);
        if (isset($matches[1])) {
            $accessToken = $matches[1];
            return $accessToken;
        }
    }
}
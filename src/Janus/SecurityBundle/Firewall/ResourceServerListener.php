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

    public function __construct(SecurityContextInterface $securityContext,
                                AuthenticationManagerInterface $authenticationManager)
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
    }

    public function handle(GetResponseEvent $event)
    {
        /*
         * https://github.com/arnaud-lb/oauth2-php/pull/1
         */
        $headers = apache_request_headers();

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

    private function getAccessToken($headers)
    {
        $accessToken = null;

        if(isset($headers['Authorization'])){
            $matches = array();
            preg_match('/bearer (.*)/', $headers['Authorization'], $matches);
            if(isset($matches[1])){
                $accessToken = $matches[1];
            }
        }
        return $accessToken;
    }
}
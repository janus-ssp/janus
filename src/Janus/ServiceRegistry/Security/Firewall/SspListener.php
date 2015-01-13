<?php

namespace Janus\ServiceRegistry\Security\Firewall;

use Janus\ServiceRegistry\Security\Authentication\Token\SspToken;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\DefaultCsrfProvider;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

class SspListener implements ListenerInterface
{
    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    /**
     * @var AuthenticationManagerInterface
     */
    private $authenticationManager;

    /**
     * @var CsrfProviderInterface
     */
    private $csrfProvider;

    /**
     * @param SecurityContextInterface $securityContext
     * @param AuthenticationManagerInterface $authenticationManager
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        AuthenticationManagerInterface $authenticationManager,
        LoggerInterface $logger,
        CsrfProviderInterface $csrfProvider
    ) {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->logger = $logger;
        $this->csrfProvider = $csrfProvider;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->headers->has('cookie')) {
            return;
        }

        if (strstr($request->headers->get('cookie'), 'SimpleSAMLAuthToken') === false) {
            return;
        }

        if (!$request->query->has('csrf-token')) {
            $this->logger->notice('Ssp Firewall: Auth Token cookie but no CSRF Token');
            return;
        }

        $csrfToken = $request->query->getAlnum('csrf-token');
        if (!$this->csrfProvider->isCsrfTokenValid('api', $csrfToken)) {
            $this->logger->notice('Ssp Firewall: Invalid CSRF token for api use: ' . $csrfToken);
            return;
        }

        try {
            $authToken = $this->authenticationManager->authenticate(new SspToken());
            $this->securityContext->setToken($authToken);

        } catch (AuthenticationException $failed) {
            $this->logger->warning('Ssp Firewall: failed:' . $failed->getMessage());
            $token = $this->securityContext->getToken();
            if ($token instanceof SspToken) {
                $this->securityContext->setToken(null);
            }
            return;
        }
    }
}

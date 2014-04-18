<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Log;

use Monolog\Logger as PsrLogger;

use Janus\ServiceRegistry\DependencyInjection\AuthenticationProviderInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContext;

class AuthenticationInfoProcessor extends ContainerAware
{
    /**
     * @param  array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        if (!$this->container || !$this->container->has('security.context')) {
            return $record;
        }

        /** @var SecurityContext $securityContext */
        $securityContext = $this->container->get('security.context');
        /** @var TokenInterface $token */
        $token = $securityContext->getToken();

        if (!$token) {
            // Didn't start authentication yet
            return $record;
        }

        $username = $token->getUsername();
        $record['extra']['authenticated_username'] = $username;

        return $record;
    }
}

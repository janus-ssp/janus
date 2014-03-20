<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Log;

use Monolog\Logger as PsrLogger;

use Janus\ServiceRegistry\DependencyInjection\AuthenticationProviderInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
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
        $token = $securityContext->getToken();

        // Didn't start authentication yet
        if (!$token) {
            return $record;
        }

        $username = $token->getUsername();
        $record['extra']['authenticated_username'] = $username;

        return $record;
    }
}

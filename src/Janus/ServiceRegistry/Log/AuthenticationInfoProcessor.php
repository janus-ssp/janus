<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Log;

use Monolog\Logger as PsrLogger;

use Janus\ServiceRegistry\DependencyInjection\AuthenticationProviderInterface;

class AuthenticationInfoProcessor
{
    /** @var  AuthenticationProviderInterface */
    private $authenticationProvider;

    /**
     * @param AuthenticationProviderInterface $authenticationProvider
     */
    public function __construct(
        AuthenticationProviderInterface $authenticationProvider
    )
    {
        $this->authenticationProvider = $authenticationProvider;
    }

    /**
     * @param  array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        // Note that installing does not require a user to be logged in
        $inInstallMode = defined('JANUS_INSTALL_MODE');
        if ($inInstallMode) {
            $username = 'Anonymous install';
        } else {
            $username = $this->authenticationProvider->getLoggedInUsername();
        }
        $record['extra']['authenticated_username'] = $username;

        return $record;
    }
}
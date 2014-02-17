<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Log;

use Monolog\Logger as PsrLogger;

use Janus\ServiceRegistry\DependencyInjection\AuthenticationProviderInterface;

/**
 * Logs info message including username suffix
 *
 * @todo find out how this can be done neater
 */
class Logger extends PsrLogger
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
     * @inheritDoc
     */
    public function log($level, $message, array $context = array())
    {
        $username = $this->authenticationProvider->getLoggedInUsername();
        $message = $message . " (User: '{$username}')";

        parent::log($level, $message, $context);
    }
}
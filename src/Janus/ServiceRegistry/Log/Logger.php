<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Log;

use Monolog\Logger as PsrLogger;

use Janus\ServiceRegistry\DependencyInjection\AuthenticationProviderInterface;

class Logger
{
    /**
     * @var PsrLogger
     */
    private $psrLogger;


    /** @var  AuthenticationProviderInterface */
    private $authenticationProvider;

    /**
     * @param $PsrLogger
     * @param AuthenticationProviderInterface $authenticationProvider
     */
    public function __construct(
        PsrLogger $PsrLogger,
        AuthenticationProviderInterface $authenticationProvider
    )
    {
        $this->psrLogger = $PsrLogger;
        $this->authenticationProvider = $authenticationProvider;
    }

    /**
     * Logs info message including usename suffix
     *
     * @param string $message
     */
    public function info($message)
    {
        $this->log($message, PsrLogger::INFO);
    }

    private function log($message, $level = PsrLogger::INFO)
    {
        $username = $this->authenticationProvider->getLoggedInUsername();
        $message = $message . " (User: '{$username}')";

        $this->psrLogger->log($level, $message);
    }
}
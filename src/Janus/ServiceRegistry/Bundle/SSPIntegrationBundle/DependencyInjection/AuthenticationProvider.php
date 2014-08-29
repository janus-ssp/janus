<?php

namespace Janus\ServiceRegistry\Bundle\SSPIntegrationBundle\DependencyInjection;

use RuntimeException;

use Janus\ServiceRegistry\DependencyInjection\AuthenticationProviderInterface;
use SimpleSAML_Session;
use Janus\ServiceRegistry\SimpleSamlPhp\ConfigProxy;

class AuthenticationProvider
    implements AuthenticationProviderInterface
{
    /**
     * @var ConfigProxy
     */
    private $config;

    /**
     * @var string
     */
    private static $allowNoAuthenticatedUser = false;

    /**
     * @param ConfigProxy $config
     */
    public function __construct(
        ConfigProxy $config
    ) {
        $this->config = $config;
    }

    /**
     * Make the Authentication Provider return null.
     */
    public static function allowNoAuthenticatedUser()
    {
        static::$allowNoAuthenticatedUser = true;
    }

    /**
     * @return string
     * @throws RuntimeException
     */
    public function getLoggedInUsername()
    {
        if (static::$allowNoAuthenticatedUser) {
            return null;
        }

        /** @var string $authenticationType */
        $authenticationType = $this->config->getValue('auth', 'login-admin');

        if (php_sapi_name() === 'cli') {
            return $authenticationType;
        }

        $session = SimpleSAML_Session::getInstance();
        if (!$session->isValid($authenticationType)) {
            throw new RuntimeException("Authsource '$authenticationType' is invalid");
        }

        /** @var string $userIdAttributeName */
        $userIdAttributeName = $this->config->getValue('useridattr', 'eduPersonPrincipalName');

        // Check if userid exists
        $attributes = $session->getAttributes();
        if (!isset($attributes[$userIdAttributeName])) {
            throw new RuntimeException("Attribute '$userIdAttributeName' with User ID is missing.");
        }

        return $attributes[$userIdAttributeName][0];
    }
}

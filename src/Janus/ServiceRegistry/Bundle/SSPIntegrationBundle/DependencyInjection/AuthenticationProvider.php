<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Bundle\SSPIntegrationBundle\DependencyInjection;

use RuntimeException;

use Janus\ServiceRegistry\DependencyInjection\AuthenticationProviderInterface;
use SimpleSAML_Session;
use SimpleSAML_Configuration;

/**
 * @author Lucas van lierop
 */
class AuthenticationProvider
    implements AuthenticationProviderInterface
{
    /**
     * @var SimpleSAML_Session
     */
    private $session;

    /**
     * @var SimpleSAML_Configuration
     */
    private $config;

    /**
     * @var string
     */
    private static $allowNoAuthenticatedUser = false;

    /**
     * @param SimpleSAML_Session $session
     * @param SimpleSAML_Configuration $config
     */
    public function __construct(
        SimpleSAML_Session $session,
        SimpleSAML_Configuration $config
    )
    {
        $this->session = $session;
        $this->config = $config;
    }

    /**
     * Override username for cases where no user is logged in
     *
     * @param string $username
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
            return;
        }

        /** @var string $authsource */
        $authsource = $this->config->getValue('auth', 'login-admin');
        /** @var string $userIdAttributeName */
        $userIdAttributeName = $this->config->getValue('useridattr', 'eduPersonPrincipalName');

        if (php_sapi_name() === 'cli') {
            return $authsource;
        }

        if (!$this->session->isValid($authsource)) {
            throw new RuntimeException("Authsource is invalid");
        }

        $attributes = $this->session->getAttributes();
        // Check if userid exists
        if (!isset($attributes[$userIdAttributeName])) {
            throw new RuntimeException('User ID is missing');
        }
        return $attributes[$userIdAttributeName][0];
    }
}

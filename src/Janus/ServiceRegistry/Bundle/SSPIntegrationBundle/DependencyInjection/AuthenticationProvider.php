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
     * @return string
     * @throws RuntimeException
     */
    public function getLoggedInUsername()
    {
        /** @var string $authsource */
        $authsource = $this->config->getValue('auth', 'login-admin');
        /** @var string $userIdAttributeName */
        $userIdAttributeName = $this->config->getValue('useridattr', 'eduPersonPrincipalName');

        $inInstallMode = defined('JANUS_INSTALL_MODE');
        if ($inInstallMode || php_sapi_name() === 'cli') {
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

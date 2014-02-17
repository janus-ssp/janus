<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\SSPIntegrationBundle\DependencyInjection;

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
        $authsource = $this->config->getValue('auth', 'login-admin');
        $useridattr = $this->config->getValue('useridattr', 'eduPersonPrincipalName');

        if (php_sapi_name() == 'cli') {
            $username = $authsource;
        } else {
            if (!$this->session->isValid($authsource)) {
                throw new RuntimeException("Authsource is invalid");
            }
            $attributes = $this->session->getAttributes();
            // Check if userid exists
            if (!isset($attributes[$useridattr])) {
                throw new RuntimeException('User ID is missing');
            }
            $username = $attributes[$useridattr][0];
        }

        return $username;
    }
}
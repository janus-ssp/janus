<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\DependencyInjection;

use Janus\ServiceRegistry\Entity\User;

interface AuthenticationProviderInterface
{
    /**
     * @return User
     */
    public function getLoggedInUsername();
}
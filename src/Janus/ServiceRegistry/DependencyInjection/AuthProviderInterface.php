<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\DependencyInjection;

use Janus\ServiceRegistry\Entity\User;

interface AuthProviderInterface
{
    /**
     * @return User
     */
    public function getLoggedInUsername();
}
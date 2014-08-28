<?php

namespace Janus\ServiceRegistry\DependencyInjection;

use Janus\ServiceRegistry\Entity\User;

interface AuthenticationProviderInterface
{
    /**
     * @return User
     */
    public function getLoggedInUsername();
}
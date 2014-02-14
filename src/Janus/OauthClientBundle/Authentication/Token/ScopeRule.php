<?php

namespace Janus\OauthClientBundle\Authentication\Token;

use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * Role based on the OAuth2 scopes
 *
 */
class ScopeRule implements RoleInterface
{
    private $role;

    /**
     * Constructor.
     *
     * @param string $role The role name
     */
    public function __construct($role)
    {
        $this->role = (string)$role;
    }

    /**
     * {@inheritdoc}
     */
    public function getRole()
    {
        return $this->role;
    }
}
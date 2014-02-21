<?php

namespace Janus\ServiceRegistry\Bundle\OauthClientBundle\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;

use Janus\ServiceRegistry\Bundle\OauthClientBundle\Authentication\Token\ScopeRule;

class ResourceServerToken implements TokenInterface
{

    private $accessToken;
    private $user;
    private $prefix;

    public function __construct($accessToken, $prefix = 'ROLE_')
    {
        $this->accessToken = $accessToken;
        $this->prefix = $prefix;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return json_encode($this->user);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        return json_decode($serialized, true);
    }

    /**
     * Returns a string representation of the Token.
     *
     * This is only to be used for debugging purposes.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->serialize();
    }

    /**
     * Returns the user roles.
     *
     * @return RoleInterface[] An array of RoleInterface instances.
     */
    public function getRoles()
    {
        $scopes = $this->user['scopes'];
        $roles = array();
        foreach ($scopes as $scope) {
            $role = strtoupper($scope);
            $roles[] = new ScopeRule(0 === strpos($role, $this->prefix) ? $role : $this->prefix . $role);
        }
        return $roles;
    }

    /**
     * Returns the user credentials.
     *
     * @return mixed The user credentials
     */
    public function getCredentials()
    {
        return $this->accessToken;
    }

    /**
     * Returns a user representation.
     *
     * @return mixed either returns an object which implements __toString(), or
     *                  a primitive string is returned.
     */
    public function getUser()
    {
        return $this->user['principal']['name'];
    }

    /**
     * Sets a user.
     *
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Returns the username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->user['principal']['name'];
    }

    /**
     * Returns whether the user is authenticated or not.
     *
     * @return Boolean true if the token has been authenticated, false otherwise
     */
    public function isAuthenticated()
    {
        return $this->user ? true : false;
    }

    /**
     * Sets the authenticated flag.
     *
     * @param Boolean $isAuthenticated The authenticated flag
     */
    public function setAuthenticated($isAuthenticated)
    {
        if ($isAuthenticated && !$this->user) {
            throw new \InvalidArgumentException("First set the User before marking the Token as Authenticated ");
        }
        if (!$isAuthenticated) {
            $this->user = null;
        }
    }

    /**
     * Removes sensitive information from the token.
     */
    public function eraseCredentials()
    {
    }

    /**
     * Returns the token attributes.
     *
     * @return array The token attributes
     */
    public function getAttributes()
    {
        return isset($this->user['principal']['attributes']) ? $this->user['principal']['attributes'] : array();
    }

    /**
     * Sets the token attributes.
     *
     * @param array $attributes The token attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->user['principal']['attributes'] = $attributes;
    }

    /**
     * Returns true if the attribute exists.
     *
     * @param string $name The attribute name
     *
     * @return Boolean true if the attribute exists, false otherwise
     */
    public function hasAttribute($name)
    {
        return isset($this->user['principal']['attributes'][$name]);
    }

    /**
     * Returns an attribute value.
     *
     * @param string $name The attribute name
     *
     * @return mixed The attribute value
     *
     * @throws \InvalidArgumentException When attribute doesn't exist for this token
     */
    public function getAttribute($name)
    {
        if (isset($this->user['principal']['attributes'][$name])) {
            return $this->user['principal']['attributes'][$name];
        }
        throw new \InvalidArgumentException("$name is not a valid attribute name");
    }

    /**
     * Sets an attribute.
     *
     * @param string $name  The attribute name
     * @param mixed $value The attribute value
     */
    public function setAttribute($name, $value)
    {
        if (!isset($this->user['principal']['attributes'])) {
            $this->user['principal']['attributes'] = array();
        }
        $this->user['principal']['attributes'][$name] = $value;
    }
}

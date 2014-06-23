<?php

// src/Acme/DemoBundle/Security/Authentication/Provider/WsseProvider.php
namespace Janus\ServiceRegistry\Security\Authentication\Provider;

use Janus\ServiceRegistry\Security\Authentication\Token\SspToken;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Janus\ServiceRegistry\Entity\User;

class SspProvider implements AuthenticationProviderInterface
{
    private $userProvider;
    private $config;

    public function __construct(UserProviderInterface $userProvider, \SimpleSAML_Configuration $config)
    {
        $this->userProvider = $userProvider;
        $this->config = $config;
    }

    public function authenticate(TokenInterface $token)
    {
        /** @var string $authenticationType */
        $authenticationType = $this->config->getValue('auth', 'login-admin');

        if (php_sapi_name() === 'cli') {
            return $this->getTokenForUsername($authenticationType);
        }

        $session = \SimpleSAML_Session::getInstance();
        if (!$session->isValid($authenticationType)) {
            throw new AuthenticationException("Authsource '$authenticationType' is invalid");
        }

        /** @var string $userIdAttributeName */
        $userIdAttributeName = $this->config->getValue('useridattr', 'eduPersonPrincipalName');

        // Check if userid exists
        $attributes = $session->getAttributes();
        if (!isset($attributes[$userIdAttributeName])) {
            throw new AuthenticationException("Attribute '$userIdAttributeName' with User ID is missing.");
        }

        return $this->getTokenForUsername($attributes[$userIdAttributeName][0]);
    }

    private function getTokenForUsername($username, $attributes = array())
    {
        /** @var User $user */
        $user = $this->userProvider->loadUserByUsername($username);

        if (!$user) {
            throw new AuthenticationException("No user by the name of '$username'");
        }

        $token = new SspToken($user->getRoles());
        $token->setUser($user);
        $token->setAttributes($attributes);
        return $token;
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof SspToken;
    }
}

<?php

namespace Janus\ServiceRegistry\Bundle\OauthClientBundle\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;

class ResourceServerSecurityFactory implements SecurityFactoryInterface
{
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.resource.server.' . $id;
        $container->setDefinition($providerId, new DefinitionDecorator('resource.server.security.authentication.provider'));

        $listenerId = 'security.authentication.listener.resource.server.' . $id;
        $container->setDefinition($listenerId, new DefinitionDecorator('resource.server.security.authentication.listener'));

        return array($providerId, $listenerId, $defaultEntryPoint);
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'oauth2';
    }

    public function addConfiguration(NodeDefinition $node)
    {
    }
}
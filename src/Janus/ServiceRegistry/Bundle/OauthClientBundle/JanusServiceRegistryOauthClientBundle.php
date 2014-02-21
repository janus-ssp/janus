<?php

namespace Janus\ServiceRegistry\Bundle\OauthClientBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Janus\ServiceRegistry\Bundle\OauthClientBundle\DependencyInjection\Security\Factory\ResourceServerSecurityFactory;

class JanusServiceRegistryOauthClientBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new ResourceServerSecurityFactory());
    }
}

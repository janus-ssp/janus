<?php

namespace Janus\ConnectionsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Janus\SecurityBundle\DependencyInjection\Security\Factory\ResourceServerSecurityFactory;

class JanusConnectionsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new ResourceServerSecurityFactory());
    }
}

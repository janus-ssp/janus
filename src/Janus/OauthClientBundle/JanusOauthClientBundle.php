<?php

namespace Janus\OauthClientBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Janus\OauthClientBundle\DependencyInjection\Security\Factory\ResourceServerSecurityFactory;

class JanusOauthClientBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new ResourceServerSecurityFactory());
    }
}

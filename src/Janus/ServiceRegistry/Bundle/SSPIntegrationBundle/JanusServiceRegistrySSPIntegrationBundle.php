<?php

namespace Janus\ServiceRegistry\Bundle\SSPIntegrationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Janus\ServiceRegistry\Bundle\SSPIntegrationBundle\DependencyInjection\DatabasePass;

class JanusServiceRegistrySSPIntegrationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }
}
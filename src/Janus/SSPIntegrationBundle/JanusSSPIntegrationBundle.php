<?php

namespace Janus\SSPIntegrationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Janus\SSPIntegrationBundle\DependencyInjection\DatabasePass;

class JanusSSPIntegrationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }
}
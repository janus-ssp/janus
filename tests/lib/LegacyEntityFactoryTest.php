<?php
use Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection\ConfigProxy;

class LegacyEntityFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testCreatesEntity()
    {
        $config = new ConfigProxy(array());
        $factory = new LegacyEntityFactory($config);

        $this->assertInstanceOf('sspmod_janus_Entity', $factory->create());
    }
} 
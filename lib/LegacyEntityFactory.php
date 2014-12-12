<?php

use Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection\ConfigProxy;

class LegacyEntityFactory
{
    /** @var ConfigProxy */
    private $config;

    public function __construct(ConfigProxy $configProxy)
    {
        $this->config = $configProxy;
    }

    /**
     * @return sspmod_janus_Entity
     */
    public function create()
    {
        return new sspmod_janus_Entity($this->config);
    }
} 
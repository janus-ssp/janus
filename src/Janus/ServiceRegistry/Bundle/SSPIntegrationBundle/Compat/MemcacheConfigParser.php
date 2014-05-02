<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Bundle\SSPIntegrationBundle\Compat;

class MemcacheConfigParser
{
    /**
     * Converts SimpleSample memcache config to params Memcache::addServer requires
     */
    public function parse(array $memcacheServerGroupsConfig)
    {
        if (empty($memcacheServerGroupsConfig)) {
            throw new \Exception('Memcache cannot be used  since no servers are configured');
        }

        $config = array();

        foreach ($memcacheServerGroupsConfig as $serverGroup) {
            foreach ($serverGroup as $serverGroupName => $server) {
                $config[$serverGroupName][] = $this->parseServer($server);
            };
        }

        return $config;
    }

    /**
     * @param array $server
     * @return array
     */
    private function parseServer(array $server)
    {
        $params = array(
            'hostname' => null,
            'port' => null,
            'weight' => null,
            'timeout' => null,
        );

        foreach($params as $name => $value) {
            if (isset($server[$name])) {
                $params[$name] = $server[$name];
            }
        }

        return $params;
    }
}

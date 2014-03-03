<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Bundle\SSPIntegrationBundle\DependencyInjection;

use Memcache;

class MemcacheFactory
{
    /**
     * @param array $serverGroups
     * @return Memcache
     * @throws \Exception
     */
    public static function create(array $serverGroups)
    {
        if (!extension_loaded('memcache')) {
            throw new \Exception('Memcache cannot be used as since it is not installed or loaded');
        }

        $memcache = new Memcache();
        foreach ($serverGroups as $serverGroup) {
            foreach ($serverGroup as $server) {
                $createParams = function ($server) {
                    // Set hostname
                    $params = array($server['hostname']);

                    // Set port
                    if (!isset($server['port'])) {
                        return $params;
                    }
                    $params[] = $server['port'];

                    // Set weight  and non configurable persistence
                    if (!isset($server['weight'])) {
                        return $params;
                    }
                    $params[] = null; // Persistent
                    $params[] = $server['weight'];

                    // Set Timeout and non configurable interval/status/failure callback
                    if (!isset($server['timeout'])) {
                        return $params;
                    }
                    $params[] = null; // Retry interval
                    $params[] = null; // Status
                    $params[] = null; // Failure callback
                    $params[] = $server['timeout'];

                    return $params;
                };
                call_user_func_array(array($memcache, 'addserver'), $createParams($server));
            }
        }

        return $memcache;
    }
}
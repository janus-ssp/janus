<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Value;

class Ip
{
    /**
     * @var string
     */
    private $ip;

    /**
     * @param string $ip
     * @throws \InvalidArgumentException
     */
    public function __construct($ip)
    {
        if (!@inet_pton($ip)) {
            throw new \InvalidArgumentException("IP '{$ip}' is not a valid address");
        }

        $this->ip = $ip;
    }

    public function __toString()
    {
        return $this->ip;
    }
}
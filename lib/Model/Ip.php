<?php

class sspmod_janus_Model_Ip
{
    /**
     * @var string
     */
    private $ip;

    /**
     * @param string $ip
     * @throws Exception
     */
    public function __construct($ip)
    {
        if (!@inet_pton($ip)) {
            throw new Exception("IP '{$ip}' is not a valid address");
        }

        $this->ip = $ip;
    }

    public function __toString()
    {
        return $this->ip;
    }
}
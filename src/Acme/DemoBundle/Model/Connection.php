<?php

namespace Acme\DemoBundle\Model;

class Connection
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $secret;

    /**
     * @var string The connection message
     */
    public $message;

    /**
     * String representation for a connection
     *
     * @return string
     */
    public function __toString() {
        return $this->message;
    }
}
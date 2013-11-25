<?php

namespace Liip\HelloBundle;

use JMS\Serializer\Annotation as Serializer;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\Collections\Collection;

class Response
{
    /**
     * @var Collection
     * @Serializer\XmlList(inline = true, entry = "connection")
     */
    protected $connections;

    public function __construct($connections)
    {
        if (is_array($connections)) {
            $connections = new ArrayCollection($connections);
        } elseif (!$connections instanceof Collection) {
            throw new \RuntimeException('Response requires a Collection or an array');
        }

        $this->connections = $connections;
    }

    public function getConnections()
    {
        return $this->connections;
    }
}

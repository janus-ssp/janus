<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Connection;

use Janus\ServiceRegistry\Connection\ConnectionDto;
use Janus\ServiceRegistry\Entity\Connection;

class ConnectionDtoCollection
{
    /**
     * @var Connection[]
     */
    public $connections;

    /**
     * @var integer
     */
    public $offset;

    /**
     * @var integer
     */
    public $limit;

    /**
     * @param ConnectionDto[] $connections
     * @param integer $offset
     * @param integer $limit
     */
    public function __construct(array $connections = array(), $offset = null, $limit = null)
    {
        foreach ($connections as $connection) {
            $this->addConnection($connection);
        }

        $this->offset = $offset;
        $this->limit = $limit;
    }

    /**
     * @param ConnectionDto $connectionDto
     * @return $this
     */
    public function addConnection(ConnectionDto $connectionDto)
    {
        $this->connections[$connectionDto->getType()][$connectionDto->getId()] = $connectionDto;
        return $this;
    }
}

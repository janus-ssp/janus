<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Bundle\CoreBundle\Model;

use Janus\ServiceRegistry\Connection\ConnectionDto;
use Janus\ServiceRegistry\Entity\Connection;

class ConnectionCollection
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
    public function __construct($connections = array(), $offset = null, $limit = null)
    {
        $this->connections = $connections;
        $this->offset = $offset;
        $this->limit = $limit;
    }
}

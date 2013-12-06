<?php

namespace Janus\ConnectionsBundle\Model;

use sspmod_janus_Model_Connection_Revision_Dto;

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
     * @param sspmod_janus_Model_Connection_Revision_Dto[] $connections
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
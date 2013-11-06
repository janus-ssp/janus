<?php

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *  name="allowedEntity"
 * )
 */
class sspmod_janus_Model_Connection_Revision_AllowedConnectionRelation
{
    /**
     * @var sspmod_janus_Model_Connection_Revision
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="sspmod_janus_Model_Connection_Revision")
     * @ORM\JoinColumn(name="entityRevisionId", referencedColumnName="id", onDelete="cascade")
     */
    protected $connectionRevision;

    /**
     * @var sspmod_janus_Model_Connection
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="sspmod_janus_Model_Connection")
     * @ORM\JoinColumn(name="remoteeid", referencedColumnName="eid", onDelete="cascade")
     */
    protected $remoteConnection;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="janusDateTime")
     */
    protected $createdAtDate;

    /**
     * @var sspmod_janus_Model_Ip
     *
     * @ORM\Column(name="ip", type="janusIp")
     */
    protected $updatedFromIp;

    /**
     * @param sspmod_janus_Model_Connection_Revision $connectionRevision
     * @param sspmod_janus_Model_Connection $remoteConnection
     */
    public function __construct(
        sspmod_janus_Model_Connection_Revision $connectionRevision,
        sspmod_janus_Model_Connection $remoteConnection
    ) {
        $this->connectionRevision = $connectionRevision;
        $this->remoteConnection = $remoteConnection;
    }

    /**
     * @param \DateTime $createdAtDate
     * @return $this
     */
    public function setCreatedAtDate(DateTime $createdAtDate)
    {
        $this->createdAtDate = $createdAtDate;
        return $this;
    }

    /**
     * @param sspmod_janus_Model_Ip $updatedFromIp
     * @return $this
     */
    public function setUpdatedFromIp(sspmod_janus_Model_Ip $updatedFromIp)
    {
        $this->updatedFromIp = $updatedFromIp;
        return $this;
    }
}
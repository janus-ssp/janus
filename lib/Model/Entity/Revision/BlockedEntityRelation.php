<?php

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *  name="blockedEntity"
 * )
 */
class sspmod_janus_Model_Entity_Revision_BlockedEntityRelation
{
    /**
     * @var sspmod_janus_Model_Entity_Revision
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="sspmod_janus_Model_Entity_Revision")
     * @ORM\JoinColumn(name="entityRevisionId", referencedColumnName="id", onDelete="cascade")
     */
    protected $entityRevision;

    /**
     * @var sspmod_janus_Model_Entity
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="sspmod_janus_Model_Entity")
     * @ORM\JoinColumn(name="remoteeid", referencedColumnName="eid", onDelete="cascade")
     */
    protected $remoteEntity;

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
     * @param sspmod_janus_Model_Entity_Revision $entityRevision
     * @param sspmod_janus_Model_Entity $remoteEntity
     */
    public function __construct(
        sspmod_janus_Model_Entity_Revision $entityRevision,
        sspmod_janus_Model_Entity $remoteEntity
    ) {
        $this->entityRevision = $entityRevision;
        $this->remoteEntity = $remoteEntity;
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
<?php

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *  name="blockedEntity"
 * )
 */
class sspmod_janus_Model_Entity_BlockedEntityRelation
{
    /**
     * @var sspmod_janus_Model_Entity
     *
     * @ORM\ManyToOne(targetEntity="sspmod_janus_Model_Entity")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="eid", referencedColumnName="eid"),
     *      @ORM\JoinColumn(name="revisionid", referencedColumnName="revisionid")
     * })
     */
    protected $entity;

    /**
     * @var sspmod_janus_Model_Entity_Id
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="sspmod_janus_Model_Entity_Id")
     * @ORM\JoinColumn(name="remoteeid", referencedColumnName="eid")
     */
    protected $remoteEntityId;

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
     * @param sspmod_janus_Model_Entity $entity
     * @param sspmod_janus_Model_Entity_Id $remoteEntityId
     */
    public function __construct(
        sspmod_janus_Model_Entity $entity,
        sspmod_janus_Model_Entity_Id $remoteEntityId
    ) {
        $this->entity = $entity;
        $this->remoteEntityId = $remoteEntityId;
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
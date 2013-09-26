<?php

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *  name="hasEntity"
 * )
 */
class sspmod_janus_Model_User_EntityRelation
{
    /**
     * @var sspmod_janus_Model_User
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="sspmod_janus_Model_User")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="uid", referencedColumnName="uid", nullable=true)
     * })
     */
    protected $user;

    /**
     * @var int
     *
     * @ORM\Id
     *
     * @ORM\Column(name="eid", type="integer")
     */
    protected $entityId;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="janusDateTime", nullable=true)
     */
    protected $createdAtDate;

    /**
     * @var sspmod_janus_Model_Ip
     *
     * @ORM\Column(name="ip", type="janusIp", nullable=true)
     */
    protected $updatedFromIp;

    /**
     * @param sspmod_janus_Model_User $user
     * @param sspmod_janus_Model_Entity $entity
     */
    public function __construct(
        sspmod_janus_Model_User $user,
        sspmod_janus_Model_Entity $entity
    ) {
        $this->user = $user;
        $this->entityId = $entity->getId();
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
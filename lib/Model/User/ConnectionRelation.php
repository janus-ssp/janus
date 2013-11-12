<?php

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *  name="hasEntity"
 * )
 */
class sspmod_janus_Model_User_ConnectionRelation
{
    /**
     * @var sspmod_janus_Model_User
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="sspmod_janus_Model_User")
     * @ORM\JoinColumn(name="uid", referencedColumnName="uid", nullable=true, onDelete="cascade")
     */
    protected $user;

    /**
     * @var sspmod_janus_Model_Connection
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="sspmod_janus_Model_Connection")
     * @ORM\JoinColumn(name="eid", referencedColumnName="eid", onDelete="cascade")
     */
    protected $connection;

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
     * @param sspmod_janus_Model_Connection $connection
     */
    public function __construct(
        sspmod_janus_Model_User $user,
        sspmod_janus_Model_Connection $connection
    ) {
        $this->user = $user;
        $this->connection = $connection;
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
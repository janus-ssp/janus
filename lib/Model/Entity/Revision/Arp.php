<?php

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *  name="arp"
 * )
 */
class sspmod_janus_Model_Entity_Revision_Arp
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="aid", type="integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_default", type="boolean", nullable=true)
     */
    protected $isDefault;

    /**
     * @var string
     *
     * @ORM\Column(name="attributes", type="text", nullable=true)
     */
    protected $attributes;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="janusDateTime")
     */
    protected $createdAtDate;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="updated", type="janusDateTime")
     */
    protected $updatedAtDate;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="deleted", type="janusDateTime")
     * @todo convert to nullable
     */
    protected $deletedAtDate = '';

    /**
     * @var sspmod_janus_Model_Ip
     *
     * @ORM\Column(name="ip", type="janusIp")
     */
    protected $updatedFromIp;

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
     * @param \DateTime $updatedAtDate
     * @return $this
     */
    public function setUpdatedAtDate(DateTime $updatedAtDate)
    {
        $this->updatedAtDate = $updatedAtDate;
        return $this;
    }

    /**
     * @param \DateTime $deleteAtDate
     * @return $this
     */
    public function setDeletedAtDate(DateTime $deleteAtDate)
    {
        $this->deletedAtDate = $deleteAtDate;
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
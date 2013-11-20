<?php

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *  name="subscription"
 * )
 */
class sspmod_janus_Model_User_Subscription
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="sid", type="integer")
     */
    protected $id;

    /**
     * @var sspmod_janus_Model_User
     *
     * @ORM\ManyToOne(targetEntity="sspmod_janus_Model_User")
     * @ORM\JoinColumn(name="uid", referencedColumnName="uid", nullable=false, onDelete="cascade")
     */
    protected $user;

    /**
     * @var string
     *
     * @ORM\Column(name="subscription", type="text")
     */
    protected $address;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="text", nullable=true)
     */
    protected $type;

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
     * @param string $address
     * @param string $type
     */
    public function __construct(
        sspmod_janus_Model_User $user,
        $address,
        $type
    ) {
        $this->user = $user;
        $this->setAddress($address);
        $this->setType($type);
    }

    /**
     * @param string $type
     */
    public function update(
        $type
    )
    {
        $this->setType($type);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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

    /**
     * @param string $address
     * @throws \InvalidArgumentException
     * @return sspmod_janus_Model_User_Subscription
     */
    private function setAddress($address)
    {
        if (empty($address)) {
            throw new \InvalidArgumentException("Invalid address '{$address}''");
        }

        $this->address = $address;

        return $this;
    }
    
    /**
     * @param string $type
     * @throws \InvalidArgumentException
     * @return sspmod_janus_Model_User_Subscription
     */
    private function setType($type)
    {
        if (empty($type)) {
            throw new \InvalidArgumentException("Invalid type '{$type}''");
        }

        $this->type = $type;

        return $this;
    }
}
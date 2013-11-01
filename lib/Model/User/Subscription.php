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
    protected $subscription;

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
     * @param $subscription
     */
    public function __construct(
        sspmod_janus_Model_User $user,
        $subscription
    ) {
        $this->user = $user;
        $this->setSubscription($subscription);
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
     * @param string $subscription
     * @throws Exception
     * @return sspmod_janus_Model_User_Message
     */
    private function setSubscription($subscription)
    {
        if (empty($subscription)) {
            throw new Exception("Invalid subscription '{$subscription}''");
        }

        $this->subscription = $subscription;

        return $this;
    }
}
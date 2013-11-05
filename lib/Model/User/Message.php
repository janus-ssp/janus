<?php

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *  name="message"
 * )
 */
class sspmod_janus_Model_User_Message
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="mid")
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
     * @ORM\Column(name="subject", type="text")
     */
    protected $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    protected $message;

    /**
     * @var sspmod_janus_Model_User
     *
     * @ORM\ManyToOne(targetEntity="sspmod_janus_Model_User")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="`from`", referencedColumnName="uid", nullable=false)
     * })
     */
    protected $from;

    /**
     * @var string
     *
     * @ORM\Column(name="subscription", type="text")
     */
    protected $subscription;

    /**
     * @var bool
     *
     * @ORM\Column(name="`read`", type="janusBoolean", options={"default" = "no"})
     */
    protected $isRead = false;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="janusDateTime")
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
     * @param string $subject
     * @param string $message
     * @param sspmod_janus_Model_User $from
     * @param string $subscription
     */
    public function __construct(
        sspmod_janus_Model_User $user,
        $subject,
        $message,
        sspmod_janus_Model_User $from,
        $subscription
    ) {
        $this->user = $user;
        $this->setSubject($subject);
        $this->setMessage($message);
        $this->setFrom($from);
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
     * @param string $subject
     * @throws \InvalidArgumentException
     * @return sspmod_janus_Model_User_Message
     */
    private function setSubject($subject)
    {
        if (empty($subject)) {
            throw new \InvalidArgumentException("Invalid subject '{$subject}''");
        }

        $this->subject = $subject;

        return $this;
    }

    /**
     * @param string $message
     * @throws \InvalidArgumentException
     * @return sspmod_janus_Model_User_Message
     */
    private function setMessage($message)
    {
        if (empty($message)) {
            throw new \InvalidArgumentException("Invalid message '{$message}''");
        }

        $this->message = $message;

        return $this;
    }

    /**
     * @param string sspmod_janus_Model_User $from
     * @return sspmod_janus_Model_User_Message
     */
    private function setFrom(sspmod_janus_Model_User $from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @param string $subscription
     * @throws \InvalidArgumentException
     * @return sspmod_janus_Model_User_Message
     */
    private function setSubscription($subscription)
    {
        if (empty($subscription)) {
            throw new \InvalidArgumentException("Invalid subscription '{$subscription}''");
        }

        $this->subscription = $subscription;

        return $this;
    }
}
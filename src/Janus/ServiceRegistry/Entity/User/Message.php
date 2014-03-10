<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Entity\User;

use DateTime;

use Doctrine\ORM\Mapping AS ORM;

use Janus\ServiceRegistry\Value\Ip;
use Janus\ServiceRegistry\Entity\User;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *  name="message"
 * )
 */
class Message
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="mid", type="integer")
     */
    protected $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Janus\ServiceRegistry\Entity\User")
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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Janus\ServiceRegistry\Entity\User")
     * @ORM\JoinColumn(name="`from`", referencedColumnName="uid", nullable=false)
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
     * @var Ip
     *
     * @ORM\Column(name="ip", type="janusIp", nullable=true)
     */
    protected $updatedFromIp;

    /**
     * @param User $user
     * @param string $subject
     * @param string $message
     * @param User $from
     * @param string $subscription
     */
    public function __construct(
        User $user,
        $subject,
        $message,
        User $from,
        $subscription
    ) {
        $this->user = $user;
        $this->setSubject($subject);
        $this->setMessage($message);
        $this->setFrom($from);
        $this->setSubscription($subscription);
    }

    /**
     * @param DateTime $createdAtDate
     * @return $this
     */
    public function setCreatedAtDate(DateTime $createdAtDate)
    {
        $this->createdAtDate = $createdAtDate;
        return $this;
    }

    /**
     * @param Ip $updatedFromIp
     * @return $this
     */
    public function setUpdatedFromIp(Ip $updatedFromIp)
    {
        $this->updatedFromIp = $updatedFromIp;
        return $this;
    }

    /**
     * @param string $subject
     * @throws \InvalidArgumentException
     * @return Message
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
     * @return Message
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
     * @param string User $from
     * @return Message
     */
    private function setFrom(User $from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @param string $subscription
     * @throws \InvalidArgumentException
     * @return Message
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
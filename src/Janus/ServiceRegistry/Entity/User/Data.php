<?php
namespace Janus\ServiceRegistry\Entity\User;

use DateTime;
use Exception;

use Doctrine\ORM\Mapping AS ORM;

use Janus\ServiceRegistry\Value\Ip;
use Janus\ServiceRegistry\Entity\User;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *  name="userData"
 * )
 */
class Data
{
    /**
     * @var User
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Janus\ServiceRegistry\Entity\User", inversedBy="dataCollection")
     * @ORM\JoinColumn(name="uid", referencedColumnName="uid", onDelete="cascade")
     */
    protected $user;

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(name="`key`", length=255)
     */
    protected $key;

    /**
     * @var string
     *
     * @ORM\Column(name="value", length=255)
     */
    protected $value;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="`update`", type="janusDateTime")
     */
    protected $updatedAtDate;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="janusDateTime")
     */
    protected $createdAtDate;

    /**
     * @var Ip
     *
     * @ORM\Column(name="ip", type="janusIp")
     */
    protected $updatedFromIp;

    /**
     * @param User $user
     * @param $key
     * @param $value
     */
    public function __construct(
        User $user,
        $key,
        $value
    ) {
        $this->user = $user;
        $this->setKey($key);
        $this->setValue($value);
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
     * @param DateTime $updatedAtDate
     * @return $this
     */
    public function setUpdatedAtDate(DateTime $updatedAtDate)
    {
        $this->updatedAtDate = $updatedAtDate;
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
     * @param string $key
     * @throws Exception
     * @return Message
     */
    private function setKey($key)
    {
        if (empty($key)) {
            throw new Exception("Invalid key '{$key}''");
        }

        $this->key = $key;

        return $this;
    }

    /**
     * @param string $value
     * @throws Exception
     * @return Message
     */
    private function setValue($value)
    {
        if (empty($value)) {
            throw new Exception("Invalid value '{$value}''");
        }

        $this->value = $value;

        return $this;
    }
}
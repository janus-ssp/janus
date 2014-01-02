<?php
namespace Janus\Entity;

use DateTime;
use Exception;

use Doctrine\ORM\Mapping AS ORM;

use Janus\Value\Ip;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *  name="user"
 * )
 */
class User
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="uid", type="integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="userid", type="text", nullable=true)
     */
    protected $username;

    /**
     * A collection of user type names (admin, technical etc.)
     *
     * @var array
     *
     * @ORM\Column(name="type", type="janusUserType")
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(name="email", length=320, nullable=true)
     */
    protected $email;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="janusBoolean", nullable=true, options={"default" = "yes"})
     */
    protected $isActive = true;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="`update`", type="janusDateTime", nullable=true)
     */
    protected $updatedAtDate;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="janusDateTime", nullable=true)
     */
    protected $createdAtDate;

    /**
     * @var Ip
     *
     * @ORM\Column(name="ip", type="janusIp", nullable=true)
     */
    protected $updatedFromIp;

    /**
     * @var string
     *
     * @ORM\Column(name="data", type="text", nullable=true)
     */
    protected $data;

    /**
     * @var string
     *
     * @ORM\Column(name="secret", type="text", nullable=true)
     */
    protected $secret;

    /**
     * @var array
     *
     * @ORM\OneToMany(targetEntity="Janus\Entity\User\Data", mappedBy="user", fetch="LAZY")
     *
     * @todo find out what the difference between user.data column and userData table is
     */
    protected $dataCollection;

    /**
     * @param $username
     * @param array $type
     * @param string|null $email
     * @param bool $isActive
     */
    public function __construct(
        $username,
        array $type,
        $email = null,
        $isActive = true
    )
    {
        $this->setUsername($username);
        $this->type = $type;
        $this->setEmail($email);
        $this->activate($isActive);
    }

    /**
     * @param string $username
     * @param array $type
     * @param string|null $email
     * @param bool $isActive
     * @param array|null $data
     * @param string|null $secret
     */
    public function update(
        $username,
        array $type,
        $email = null,
        $isActive = true,
        $data = null,
        $secret = null
    )
    {
        $this->setUsername($username);
        $this->type = $type;
        $this->setEmail($email);
        $this->activate($isActive);
        $this->setData($data);
        $this->secret = $secret;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
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
     * @return bool
     */
    public function isActive()
    {
        return $this->isActive === true;
    }

    /**
     * @param bool $active
     * @return $this
     */
    public function activate($active = true)
    {
        $this->isActive = ($active === true);
    }

    /**
     * @param string $username
     * @return $this
     * @throws Exception
     */
    private function setUsername($username)
    {
        if (empty($username)) {
            throw new Exception("Invalid username '{$username}'");
        }

        $this->username = $username;

        return $this;
    }

    /**
     * @param string $email
     */
    private function setEmail($email)
    {
        $this->email = $email;
    }
}
<?php

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *  name="user"
 * )
 */
class sspmod_janus_Model_User
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
     * @var sspmod_janus_Model_Ip
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
     * @ORM\OneToMany(targetEntity="sspmod_janus_Model_User_Data", mappedBy="user", fetch="LAZY")
     *
     * @todo find out what the difference between user.data column and userData table is
     */
    protected $updatedByUserData;

    /**
     * @param string $username
     * @param array $type
     */
    public function __construct(
        $username,
        array $type
    )
    {
        $this->setUsername($username);
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
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
     * @param \DateTime $updatedAtDate
     * @return $this
     */
    public function setUpdatedAtDate(DateTime $updatedAtDate)
    {
        $this->updatedAtDate = $updatedAtDate;
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
}
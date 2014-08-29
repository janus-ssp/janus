<?php

namespace Janus\ServiceRegistry\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Janus\Component\ReadonlyEntities\Value\Ip;
use Janus\Component\ReadonlyEntities\Entities\User as ReadonlyUser;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *  name="user"
 * )
 */
class User extends ReadonlyUser
{
    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Janus\ServiceRegistry\Entity\User")
     * @ORM\JoinColumn(name="user", referencedColumnName="uid", nullable=true)
     */
    protected $updatedByUser;

    /**
     * @var \Doctrine\ORM\PersistentCollection
     *
     * @ORM\OneToMany(targetEntity="Janus\ServiceRegistry\Entity\Connection\Revision", mappedBy="connection", cascade={"persist", "remove"})
     */
    protected $revisions;

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
    public function setCreatedAtDate(\DateTime $createdAtDate)
    {
        $this->createdAtDate = $createdAtDate;
        return $this;
    }

    /**
     * @param \DateTime $updatedAtDate
     * @return $this
     */
    public function setUpdatedAtDate(\DateTime $updatedAtDate)
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
     * @inheritDoc
     */
    public function eraseCredentials()
    {
        $this->secret = null;
    }
}
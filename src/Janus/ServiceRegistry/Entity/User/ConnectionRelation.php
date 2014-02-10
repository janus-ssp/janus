<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Entity\User;

use DateTime;

use Doctrine\ORM\Mapping AS ORM;

use Janus\ServiceRegistry\Entity\Connection;
use Janus\ServiceRegistry\Entity\User;
use Janus\ServiceRegistry\Value\Ip;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *  name="hasConnection"
 * )
 */
class ConnectionRelation
{
    /**
     * @var User
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Janus\ServiceRegistry\Entity\User")
     * @ORM\JoinColumn(name="uid", referencedColumnName="uid", nullable=true, onDelete="cascade")
     */
    protected $user;

    /**
     @var Connection
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Janus\ServiceRegistry\Entity\Connection", inversedBy="userRelations")
     * @ORM\JoinColumn(name="eid", referencedColumnName="id", onDelete="cascade")
     */
    protected $connection;

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
     * @param User $user
     * @param Connection  $connection
     */
    public function __construct(
        User $user,
        Connection $connection
    ) {
        $this->user = $user;
        $this->connection = $connection;
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
}
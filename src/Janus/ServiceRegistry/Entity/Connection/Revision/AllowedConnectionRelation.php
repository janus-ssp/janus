<?php
namespace Janus\ServiceRegistry\Entity\Connection\Revision;

use DateTime;

use Doctrine\ORM\Mapping AS ORM;
use JMS\Serializer\Annotation AS Serializer;

use Janus\ServiceRegistry\Entity\Connection;
use Janus\ServiceRegistry\Entity\Connection\Revision;
use Janus\ServiceRegistry\Value\Ip;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *  name="allowedConnection"
 * )
 *
 */
class AllowedConnectionRelation
{
    /**
     @var Connection\Revision
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Janus\ServiceRegistry\Entity\Connection\Revision", inversedBy="allowedConnectionRelations")
     * @ORM\JoinColumn(name="connectionRevisionId", referencedColumnName="id", onDelete="cascade")
     */
    protected $connectionRevision;

    /**
     @var Connection
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Janus\ServiceRegistry\Entity\Connection")
     * @ORM\JoinColumn(name="remoteeid", referencedColumnName="id", onDelete="cascade")
     * @Serializer\Groups({"compare"})
     */
    protected $remoteConnection;

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
     * @param Revision  $connectionRevision
     * @param Connection  $remoteConnection
     */
    public function __construct(
        Revision $connectionRevision,
        Connection $remoteConnection
    ) {
        $this->connectionRevision = $connectionRevision;
        $this->remoteConnection = $remoteConnection;
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
     * @return Connection
     */
    public function getRemoteConnection()
    {
        return $this->remoteConnection;
    }
}
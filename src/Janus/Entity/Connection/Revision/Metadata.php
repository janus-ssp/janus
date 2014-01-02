<?php
namespace Janus\Entity\Connection\Revision;

use DateTime;
use Exception;

use Doctrine\ORM\Mapping AS ORM;
use JMS\Serializer\Annotation AS Serializer;

use Janus\Entity\Connection\Revision;
use Janus\Value\Ip;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *  name="metadata"
 * )
 */
class Metadata
{
    /**
     @var Connection\Revision
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Janus\Entity\Connection\Revision", inversedBy="metadata")
     * @ORM\JoinColumn(name="connectionRevisionId", referencedColumnName="id", onDelete="cascade")
     */
    protected $connectionRevision;

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(name="`key`", length=255)
     * @Serializer\Groups({"compare"})
     */
    protected $key;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text")
     * @Serializer\Groups({"compare"})
     */
    protected $value;

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
     * @param string $key
     * @param string $value
     */
    public function __construct(
        Revision $connectionRevision,
        $key,
        $value
    ) {
        $this->connectionRevision = $connectionRevision;
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
     * @param Ip $updatedFromIp
     * @return $this
     */
    public function setUpdatedFromIp(Ip $updatedFromIp)
    {
        $this->updatedFromIp = $updatedFromIp;
        return $this;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getValue()
    {
        return $this->value;
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
        if ($value === null || $value === '') {
            throw new Exception("Invalid value '{$value}''");
        }

        $this->value = $value;

        return $this;
    }
}
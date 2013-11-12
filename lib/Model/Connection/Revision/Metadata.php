<?php

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *  name="metadata"
 * )
 */
class sspmod_janus_Model_Connection_Revision_Metadata
{
    /**
     * @var sspmod_janus_Model_Connection_Revision
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="sspmod_janus_Model_Connection_Revision")
     * @ORM\JoinColumn(name="entityRevisionId", referencedColumnName="id", onDelete="cascade")
     */
    protected $connectionRevision;

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
     * @ORM\Column(name="value", type="text")
     */
    protected $value;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="janusDateTime")
     */
    protected $createdAtDate;

    /**
     * @var sspmod_janus_Model_Ip
     *
     * @ORM\Column(name="ip", type="janusIp")
     */
    protected $updatedFromIp;

    /**
     * @param sspmod_janus_Model_Connection_Revision $connectionRevision
     * @param string $key
     * @param string $value
     */
    public function __construct(
        sspmod_janus_Model_Connection_Revision $connectionRevision,
        $key,
        $value
    ) {
        $this->connectionRevision = $connectionRevision;
        $this->setKey($key);
        $this->setValue($value);
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
     * @return sspmod_janus_Model_User_Message
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
     * @return sspmod_janus_Model_User_Message
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
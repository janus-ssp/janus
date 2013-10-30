<?php

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *  name="metadata"
 * )
 */
class sspmod_janus_Model_Entity_Revision_Metadata
{
    /**
     * @var sspmod_janus_Model_Entity_Revision
     *
     * @ORM\ManyToOne(targetEntity="sspmod_janus_Model_Entity_Revision", cascade="remove")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="eid", referencedColumnName="eid"),
     *      @ORM\JoinColumn(name="revisionid", referencedColumnName="revisionid")
     * })
     */
    protected $entityRevision;

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
     * @param sspmod_janus_Model_Entity_Revision $entityRevision
     * @param string $key
     * @param string $value
     */
    public function __construct(
        sspmod_janus_Model_Entity_Revision $entityRevision,
        $key,
        $value
    ) {
        $this->entityRevision = $entityRevision;
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
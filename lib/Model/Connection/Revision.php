<?php

use Doctrine\ORM\Mapping AS ORM;
use JMS\Serializer\Annotation AS Serializer;
use sspmod_janus_Model_Connection as Connection;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *  name="entityRevision"
 * )
 */
class sspmod_janus_Model_Connection_Revision
{
    const TYPE_IDP = 'saml20-idp';
    const TYPE_SP = 'saml20-sp';

    /**
     * @var sspmod_janus_Model_Connection
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     *
     */
    protected $id;

    /**
     * @var sspmod_janus_Model_Connection
     *
     * @ORM\ManyToOne(targetEntity="sspmod_janus_Model_Connection", inversedBy="revisions")
     * @ORM\JoinColumn(name="eid", referencedColumnName="eid", nullable=false, onDelete="CASCADE")
     * @Serializer\Groups({"compare"})
     */
    protected $connection;

    /**
     * @var string
     *
     * @ORM\Column(name="entityid", type="text")
     * @Serializer\Groups({"compare"})
     *
     */
    protected $name;

    /**
     * @var int
     *
     * @ORM\Column(name="revisionid", type="integer")
     */
    protected $revisionNr;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="text", nullable=true)
     * @Serializer\Groups({"compare"})
     */
    protected $state;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="text", nullable=true)
     */
    protected $type;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="expiration", type="janusDateTime", nullable=true)
     */
    protected $expirationDate;

    /**
     * @var string
     *
     * @ORM\Column(name="metadataurl", type="text", nullable=true)
     * @Serializer\Groups({"compare"})
     */
    protected $metadataUrl;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="metadata_valid_until", type="datetime", nullable=true)
     */
    protected $metadataValidUntil;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="metadata_cache_until", type="datetime", nullable=true)
     */
    protected $metadataCacheUntil;

    /**
     * @var bool
     *
     * @ORM\Column(name="allowedall", type="janusBoolean", options={"default" = "yes"})
     * @Serializer\Groups({"compare"})
     */
    protected $allowAllEntities = true;

    /**
     * @var sspmod_janus_Model_Connection_Revision_Arp
     *
     * @ORM\ManyToOne(targetEntity="sspmod_janus_Model_Connection_Revision_Arp")
     * @ORM\JoinColumn(name="arp", referencedColumnName="aid", nullable=true)
     * @Serializer\Groups({"compare"})
     *
     */
    protected $arp;

    /**
     * @var text
     *
     * @ORM\Column(name="manipulation", type="text", columnDefinition="mediumtext", nullable=true)
     * @Serializer\Groups({"compare"})
     */
    protected $manipulation;

    /**
     * @var sspmod_janus_Model_User
     *
     * @ORM\ManyToOne(targetEntity="sspmod_janus_Model_User")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="user", referencedColumnName="uid", nullable=true)
     * })
     */
    protected $updatedByUser;

    /**
     * @var Datetime
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
     * @var int
     *
     * @ORM\Column(name="parent", type="integer", nullable=true)
     */
    protected $parentRevisionNr;

    /**
     * @var string
     *
     * @ORM\Column(name="revisionnote", type="text", nullable=true)
     */
    protected $revisionNote;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="janusBoolean", options={"default" = "yes"})
     * @Serializer\Groups({"compare"})
     *
     */
    protected $isActive = true;

    /**
     * @var array
     *
     * @ORM\OneToMany(targetEntity="sspmod_janus_Model_Connection_Revision_Metadata", mappedBy="connectionRevision", fetch="LAZY")
     * @Serializer\Groups({"compare"})
     *
     */
    protected $metadata;

    /**
     * @param sspmod_janus_Model_Connection $connection
     * @param int $revisionNr
     * @param string $type
     * @throws Exception
     */


    /**
     * @param sspmod_janus_Model_Connection $connection
     * @param int $revisionNr
     * @param int|null $parentRevisionNr
     * @param string $revisionNote
     * @param string $type on of the TYPE_XXX constants
     * @param string $state
     * @param DateTime|null $expirationDate
     * @param string|null $metadataUrl
     * @param bool $allowAllEntities
     * @param sspmod_janus_Model_Connection_Revision_Arp|null $arp
     * @param string|null $manipulation
     * @param bool $isActive
     */
    public function __construct(
        Connection $connection,
        $revisionNr,
        $parentRevisionNr = null,
        $revisionNote,
        $type,
        $state,
        \DateTime $expirationDate = null,
        $metadataUrl = null,
        $allowAllEntities,
        sspmod_janus_Model_Connection_Revision_Arp $arp = null,
        $manipulation = null,
        $isActive
    ) {
        $this->setType($type);
        $this->connection = $connection;
        $this->name = $connection->getName();
        $this->revisionNr = $revisionNr;
        $this->parentRevisionNr = $parentRevisionNr;
        $this->setRevisionNote($revisionNote);
        $this->state = $state;
        $this->expirationDate = $expirationDate;
        $this->metadataUrl = $metadataUrl;
        $this->allowAllEntities = $allowAllEntities;
        $this->arp = $arp;
        $this->manipulation = $manipulation;
        $this->isActive = $isActive;

    }

    /**
     * @param string $type
     * @return $this
     * @throws Exception
     */
    private function setType($type)
    {
        $allowedTypes = array(self::TYPE_IDP, self::TYPE_SP);
        if (!in_array($type, $allowedTypes)) {
            throw new Exception ("Unknown connection type '{$type}'");
        }

        $this->type = $type;

        return $this;
    }

    /**
     * @param string $revisionNote
     * @throws InvalidArgumentException
     */
    private function setRevisionNote($revisionNote)
    {
        if (!is_string($revisionNote) || empty($revisionNote)) {
            throw new \InvalidArgumentException("Invalid revision note '{$revisionNote}'");
        }
        $this->revisionNote = $revisionNote;
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
     * @param sspmod_janus_Model_User $updatedByUser
     * @return $this
     */
    public function setUpdatedByUser(sspmod_janus_Model_User $updatedByUser)
    {
        $this->updatedByUser = $updatedByUser;
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return sspmod_janus_Model_Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getRevisionNr()
    {
        return $this->revisionNr;
    }

    public function getMetadata()
    {
        return $this->metadata;
    }
}
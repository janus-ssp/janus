<?php

use Doctrine\ORM\Mapping AS ORM;
use JMS\Serializer\Annotation AS Serializer;
use sspmod_janus_Model_Connection as Connection;

/**
 * @ORM\Entity(
 *  repositoryClass="sspmod_Janus_Repository_Connection_RevisionRepository"
 * )
 * @ORM\Table(
 *  name="connectionRevision",
 *  uniqueConstraints={@ORM\UniqueConstraint(name="janus__entity__eid_revisionid",columns={"eid", "revisionid"})}
 * )
 */
class sspmod_janus_Model_Connection_Revision
{
    /**
     * @var sspmod_janus_Model_Connection
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id")
     */
    protected $id;

    /**
     * @var sspmod_janus_Model_Connection
     *
     * @ORM\ManyToOne(targetEntity="sspmod_janus_Model_Connection", inversedBy="revisions")
     * @ORM\JoinColumn(name="eid", referencedColumnName="id", nullable=false, onDelete="CASCADE")
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
     * @Serializer\Groups({"compare"})
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
     * @var string
     *
     * @ORM\Column(name="arp_attributes", type="array", nullable=true)
     * @Serializer\Groups({"compare"})
     *
     */
    protected $arpAttributes;

    /**
     * @var string
     *
     * @ORM\Column(name="manipulation", type="text", columnDefinition="mediumtext", nullable=true)
     *
     */
    protected $manipulationCode;

    /**
     * @var string
     *
     * @Serializer\Groups({"compare"})
     * @Serializer\Accessor(getter="getManipulationCodePresent")
     */
    protected $manipulationCodePresent;

    /**
     * @var sspmod_janus_Model_User
     *
     * @ORM\ManyToOne(targetEntity="sspmod_janus_Model_User")
     * @ORM\JoinColumn(name="user", referencedColumnName="uid", nullable=true)
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
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     * @Serializer\Groups({"compare"})
     */
    protected $notes;
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
     * @var array
     *
     * @ORM\OneToMany(targetEntity="sspmod_janus_Model_Connection_Revision_AllowedConnectionRelation", mappedBy="connectionRevision")
     * @Serializer\Groups({"compare"})
     */
    protected $allowedConnectionRelations;

    /**
     * @var array
     *
     * @ORM\OneToMany(targetEntity="sspmod_janus_Model_Connection_Revision_BlockedConnectionRelation", mappedBy="connectionRevision")
     * @Serializer\Groups({"compare"})
     */
    protected $blockedConnectionRelations;

    /**
     * @var array
     *
     * @ORM\OneToMany(targetEntity="sspmod_janus_Model_Connection_Revision_DisableConsentRelation", mappedBy="connectionRevision")
     * @Serializer\Groups({"compare"})
     */
    protected $disableConsentConnectionRelations;

    /**
     * @param sspmod_janus_Model_Connection $connection
     * @param int $revisionNr
     * @param int|null $parentRevisionNr
     * @param string $revisionNote
     * @param string $state
     * @param DateTime|null $expirationDate
     * @param string|null $metadataUrl
     * @param bool $allowAllEntities
     * @param string|null| $arpAttributes
     * @param string|null $manipulationCode
     * @param bool $isActive
     * @param string|null| $notes
     */
    public function __construct(
        Connection $connection,
        $revisionNr,
        $parentRevisionNr = null,
        $revisionNote,
        $state,
        \DateTime $expirationDate = null,
        $metadataUrl = null,
        $allowAllEntities,
        $arpAttributes = null,
        $manipulationCode = null,
        $isActive,
        $notes = null
    )
    {
        $this->connection = $connection;
        $this->name = $connection->getName();
        $this->type = $connection->getType();
        $this->revisionNr = $revisionNr;
        $this->parentRevisionNr = $parentRevisionNr;
        $this->setRevisionNote($revisionNote);
        $this->state = $state;
        $this->expirationDate = $expirationDate;
        $this->metadataUrl = $metadataUrl;
        $this->allowAllEntities = $allowAllEntities;
        $this->arpAttributes = $arpAttributes;
        $this->manipulationCode = $manipulationCode;
        $this->isActive = $isActive;
        $this->notes = $notes;
    }

    /**
     * Creates a Dto that can be used to clone a revision
     *
     * @return sspmod_janus_Model_Connection_Revision_Dto
     *
     * @todo move this to assembler class
     */
    public function toDto()
    {
        $dto = new sspmod_janus_Model_Connection_Revision_Dto();
        $dto->setId($this->connection->getId());
        $dto->setName($this->name);
        $dto->setType($this->type);
        $dto->setRevisionNr($this->revisionNr);
        $dto->setParentRevisionNr($this->parentRevisionNr);
        $dto->setRevisionNote($this->revisionNote);
        $dto->setState($this->state);
        $dto->setExpirationDate($this->expirationDate);
        $dto->setMetadataUrl($this->metadataUrl);
        $dto->setAllowAllEntities($this->allowAllEntities);
        $dto->setArpAttributes($this->arpAttributes);
        $dto->setManipulationCode($this->manipulationCode);
        $dto->setIsActive($this->isActive);
        $dto->setNotes($this->notes);

        $setAuditProperties = !empty($this->id);
        if ($setAuditProperties) {
            $dto->setCreatedAtDate($this->createdAtDate);
            $dto->setUpdatedByUser($this->updatedByUser);
            $dto->setUpdatedFromIp($this->updatedFromIp);
        }

        // @todo create metadata dto?
        $metadataCollection = array();
        /** @var $metadata sspmod_janus_Model_Connection_Revision_Metadata */
        foreach ($this->metadata as $metadata) {
            $key = preg_replace('/[.]/', '_', $metadata->getKey());
            $key = $this->correctMetaDataKey($key);
            $this->setNestedValue($metadataCollection, $key, $metadata->getValue(), '[_.:]');
        }
        $dto->setMetadata($metadataCollection);

        return $dto;
    }

    /**
     * @param string $key
     * @return string
     *
     * @todo move to strategy
     */
    public function correctMetaDataKey($key) {
        return lcfirst($key);
    }

    /**
     * Stores value in nested array specified by path
     *
     * @param   array    $haystack   by reference
     * @param   string   $path       location split by separator
     * @param   string   $value
     * @param   string   $separator  separator used (defaults to dot)
     * @return  void
     */
    private function setNestedValue(array &$haystack, $path, $value, $separator = '.')
    {
        $pathParts = preg_split("/{$separator}/", $path);
        $target =& $haystack;
        while ($partName = array_shift($pathParts)) {
            // Store value if path is found
            if (empty($pathParts)) {
                return $target[$partName] = $value;
            }

            // Get reference to nested child
            if (!array_key_exists($partName, $target)) {
                $target[$partName] = array();
            }
            $target =& $target[$partName];
        }
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
     * @return string
     */
    public function getType()
    {
        return $this->type;
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

    public function getManipulationCodePresent()
    {
        return !empty($this->manipulationCode);
    }

    public function getState()
    {
        return $this->state;
    }

    public function getUpdatedByUser()
    {
        return $this->updatedByUser;
    }

    public function getRevisionNote()
    {
        return $this->revisionNote;
    }

    public function getCreatedAtDate()
    {
        return $this->createdAtDate;
    }
}

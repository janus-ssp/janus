<?php

namespace Janus\ServiceRegistry\Connection;

use DateTime;

use Janus\ServiceRegistry\Connection\Metadata\MetadataDto;
use JMS\Serializer\Annotation AS Serializer;
use Symfony\Component\Validator\Constraints as Assert;

use Janus\ServiceRegistry\Entity\Connection;
use Janus\ServiceRegistry\Entity\User;
use Janus\ServiceRegistry\Value\Ip;

class ConnectionDto extends \ArrayObject
{
    /**
     * Unique Identifier
     *
     * @var integer
     *
     * @Serializer\Type("integer")
     * @Serializer\ReadOnly
     */
    private $id;

    /**
     * The connection itself, not serialized.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Name (or in SAML speak 'entityid')
     *
     * @var string
     *
     * @Serializer\Type("string")
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * Revision number
     *
     * @var int
     *
     * @Serializer\Type("integer")
     * @Serializer\ReadOnly
     */
    private $revisionNr;

    /**
     * State (e.g. testaccepted, prodaccepted)
     *
     * @var string
     *
     * @Serializer\Type("string")
     */
    private $state;

    /**
     * Type (e.g. saml20-sp, saml20-idp)
     *
     * @var string
     *
     * @Serializer\Type("string")
     */
    private $type;

    /**
     * Date / time the connection itself can be considered as being expired
     * 
     * @var \DateTime
     *
     * @Serializer\Type("DateTime")
     */
    private $expirationDate;

    /**
     * Url to the metadata
     *
     * @var string
     *
     * @Serializer\Type("string")
     */
    private $metadataUrl;

    /**
     * Date / time until the metadata can be considered as valid
     *
     * @var string
     *
     * @Serializer\Type("string")
     */
    private $metadataValidUntil;

    /**
     * Date / time until when the metadata can be safely cached
     *
     * @var \Datetime
     *
     * @Serializer\Type("DateTime")
     */
    private $metadataCacheUntil;

    /**
     * Are all connections allowed to connection to this connection?
     *
     * @var bool
     *
     * @Serializer\Type("boolean")
     */
    private $allowAllEntities;

    /**
     * A list of attributes that will be will released to the Service Provider (Identity Provider only)
     *
     * @var array
     *
     * @Serializer\Type("array<string, array>")
     */
    private $arpAttributes = null;

    /**
     * PHP code which can be used to manipulate a request
     *
     * @var string
     *
     * @Serializer\Type("string")
     */
    private $manipulationCode;

    /**
     * Number of the Revision this revision was based on
     *
     * @var int
     *
     * @Serializer\Type("integer")
     * @Serializer\ReadOnly
     */
    private $parentRevisionNr;

    /**
     * Note regarding this specific revision
     *
     * @var string
     *
     * @Serializer\Type("string")
     */
    private $revisionNote;

    /**
     * General note
     *
     * @var string
     *
     * @Serializer\Type("string")
     */
    private $notes;

    /**
     * Is the connection active?
     *
     * @var bool
     *
     * @Serializer\Type("boolean")
     */
    private $isActive;

    /**
     * User that made last update
     *
     * @var User
     *
     * @Serializer\Exclude
     */
    protected $updatedByUser;

    /**
     * Date / time of creation
     *
     * @var \DateTime
     *
     * @Serializer\Type("DateTime")
     * @Serializer\ReadOnly
     */
    protected $createdAtDate;

    /**
     * Date / time of last update
     *
     * @var \Datetime
     *
     * @Serializer\Type("DateTime")
     * @Serializer\ReadOnly
     */
    protected $updatedAtDate;

    /**
     * Ip from which last update took place
     *
     * @var Ip
     *
     * @Serializer\Exclude
     */
    protected $updatedFromIp;

    /**
     * Nested metadata
     *
     * @var Janus\ServiceRegistry\Connection\Metadata\MetadataDto
     *
     * @Serializer\Type("Janus\ServiceRegistry\Connection\Metadata\MetadataDto<Janus\ServiceRegistry\Connection\Metadata\MetadataDto>")
     */
    protected $metadata;

    /**
     * Connection that are allowed to connect
     *
     * @var array
     *
     * @Serializer\Type("array")
     */
    protected $allowedConnections = array();

    /**
     * Connections that are NOT allowed to connect
     *
     * @var array
     *
     * @Serializer\Type("array")
     */
    protected $blockedConnections = array();

    /**
     * Connections for which no consent is required when connecting
     *
     * @var array
     *
     * @Serializer\Type("array")
     */
    protected $disableConsentConnections = array();

    /**
     * Implemented only to show something descriptive on the connections overview
     *
     * @todo must be fixed a different way
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->name . ' (' . $this->id . ')';
    }

    /**
     * @param boolean $allowAllEntities
     *
     * @Serializer\Type("boolean")
     */
    public function setAllowAllEntities($allowAllEntities)
    {
        $this->allowAllEntities = $allowAllEntities;
    }

    /**
     * @return boolean
     */
    public function getAllowAllEntities()
    {
        return $this->allowAllEntities;
    }

    /**
     * @param array $arpAttributes
     */
    public function setArpAttributes($arpAttributes)
    {
        $this->arpAttributes = $arpAttributes;
    }

    /**
     * @return array
     */
    public function getArpAttributes()
    {
        return $this->arpAttributes;
    }

    /**
     * @param Connection $connection
     */
    public function setConnection(\Janus\ServiceRegistry\Entity\Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param \DateTime|null $expirationDate
     */
    public function setExpirationDate(\DateTime $expirationDate = null)
    {
        $this->expirationDate = $expirationDate;
    }

    /**
     * @return DateTime
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param boolean $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @param string $manipulationCode
     */
    public function setManipulationCode($manipulationCode)
    {
        $this->manipulationCode = $manipulationCode;
    }

    /**
     * @return string
     */
    public function getManipulationCode()
    {
        return $this->manipulationCode;
    }

    /**
     * @param DateTime|null $metadataCacheUntil
     */
    public function setMetadataCacheUntil(\DateTime $metadataCacheUntil = null)
    {
        $this->metadataCacheUntil = $metadataCacheUntil;
    }

    public function getMetadataCacheUntil()
    {
        return $this->metadataCacheUntil;
    }

    /**
     * @param string $metadataUrl
     */
    public function setMetadataUrl($metadataUrl)
    {
        $this->metadataUrl = $metadataUrl;
    }

    /**
     * @return string
     */
    public function getMetadataUrl()
    {
        return $this->metadataUrl;
    }

    /**
     * @param DateTime|null $metadataValidUntil
     */
    public function setMetadataValidUntil(\DateTime $metadataValidUntil = null)
    {
        $this->metadataValidUntil = $metadataValidUntil;
    }

    public function getMetadataValidUntil()
    {
        return $this->metadataValidUntil;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
    }

    /**
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param int $parentRevisionNr
     */
    public function setParentRevisionNr($parentRevisionNr)
    {
        $this->parentRevisionNr = $parentRevisionNr;
    }

    /**
     * @return int
     */
    public function getParentRevisionNr()
    {
        return $this->parentRevisionNr;
    }

    /**
     * @param string $revisionNote
     */
    public function setRevisionNote($revisionNote)
    {
        $this->revisionNote = $revisionNote;
    }

    /**
     * @return string
     */
    public function getRevisionNote()
    {
        return $this->revisionNote;
    }

    /**
     * @param int $revisionNr
     */
    public function setRevisionNr($revisionNr)
    {
        $this->revisionNr = $revisionNr;
    }

    /**
     * @return int
     */
    public function getRevisionNr()
    {
        return $this->revisionNr;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param \DateTime $createdAtDate
     */
    public function setCreatedAtDate($createdAtDate)
    {
        $this->createdAtDate = $createdAtDate;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAtDate()
    {
        return $this->createdAtDate;
    }

    /**
     * @param \Datetime $updatedAtDate
     */
    public function setUpdatedAtDate(\Datetime $updatedAtDate)
    {
        $this->updatedAtDate = $updatedAtDate;;
    }

    /**
     * @param User $updatedByUser
     *
     */
    public function setUpdatedByUser(User $updatedByUser = null)
    {
        $this->updatedByUser = $updatedByUser;
    }

    /**
     * @return int
     */
    public function getUpdatedByUserId()
    {
        return $this->updatedByUser->getId();
    }

    /**
     * @Serializer\VirtualProperty
     * @return string
     */
    public function getUpdatedByUserName()
    {
        return $this->updatedByUser->getUsername();
    }

    /**
     * @param Ip $updatedFromIp
     */
    public function setUpdatedFromIp(Ip $updatedFromIp)
    {
        $this->updatedFromIp = $updatedFromIp;
    }

    /**
     * @Serializer\VirtualProperty
     */
    public function getUpdatedFromIp()
    {
        return (string)$this->updatedFromIp;
    }

    /**
     * @param MetadataDto $metadata
     */
    public function setMetadata(MetadataDto $metadata)
    {
        $this->metadata = $metadata;
    }

    public function removeMetadata()
    {
        $this->metadata = null;
    }

    /**
     * @return MetadataDto
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param array $allowedConnections
     */
    public function setAllowedConnections(array $allowedConnections)
    {
        $this->allowedConnections = $allowedConnections;
    }

    /**
     * @return Connection[]
     */
    public function getAllowedConnections()
    {
        return $this->allowedConnections;
    }

    /**
     * @param array $blockedConnections
     */
    public function setBlockedConnections(array $blockedConnections)
    {
        $this->blockedConnections = $blockedConnections;
    }

    /**
     * @return Connection[]
     */
    public function getBlockedConnections()
    {
        return $this->blockedConnections;
    }

    /**
     * @param array $disableConsentConnections
     */
    public function setDisableConsentConnections($disableConsentConnections)
    {
        $this->disableConsentConnections = $disableConsentConnections;
    }

    /**
     * @return Connection[]
     */
    public function getDisableConsentConnections()
    {
        return $this->disableConsentConnections;
    }
}

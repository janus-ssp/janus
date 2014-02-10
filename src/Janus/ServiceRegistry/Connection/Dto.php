<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Connection;

use DateTime;

use JMS\Serializer\Annotation AS Serializer;
use Symfony\Component\Validator\Constraints as Assert;

use Janus\ServiceRegistry\Connection\NestedCollection;
use Janus\ServiceRegistry\Entity\Connection;
use Janus\ServiceRegistry\Entity\User;
use Janus\ServiceRegistry\Value\Ip;

class Dto
{
    /**
     @var Connection
     *
     * @Serializer\Type("integer")
     */
    private $id;

    /**
     @var Connection
     */
    private $connection;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var int
     *
     * @Serializer\Type("integer")
     */
    private $revisionNr;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     */
    private $state;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     */
    private $type;

    /**
     * @var \DateTime
     *
     * @Serializer\Type("DateTime")
     */
    private $expirationDate;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     */
    private $metadataUrl;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     */
    private $metadataValidUntil;

    /**
     * @var \Datetime
     *
     * @Serializer\Type("DateTime")
     */
    private $metadataCacheUntil;

    /**
     * @var bool
     *
     * @Serializer\Type("boolean")
     */
    private $allowAllEntities;

    /**
     * @var string
     *
     * @Serializer\Type("array")
     * @todo specify array serializer annotation?
     */
    private $arpAttributes;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     */
    private $manipulationCode;

    /**
     * @var int
     *
     * @Serializer\Type("integer")
     */
    private $parentRevisionNr;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     */
    private $revisionNote;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     */
    private $notes;
    /**
     * @var bool
     *
     * @Serializer\Type("boolean")
     */
    private $isActive;

    /**
     * @var User
     *
     * @Serializer\Exclude
     */
    protected $updatedByUser;

    /**
     * @var \Datetime
     *
     * @Serializer\Type("DateTime")
     */
    protected $createdAtDate;

    /**
     * @var \Ip
     *
     * @Serializer\Exclude
     */
    protected $updatedFromIp;

    /**
     * @var NestedCollection
     *
     * @Serializer\Type("Janus\ServiceRegistry\Connection\NestedCollection")
     */
    protected $metadata;

    /**
     * @var array
     *
     * @Serializer\Type("array")
     */
    protected $allowedConnections;

    /**
     * @var array
     *
     * @Serializer\Type("array")
     */
    protected $blockedConnections;

    /**
     * @var array
     *
     * @Serializer\Type("array")
     */
    protected $disableConsentConnections;

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
     * @param string $arpAttributes
     */
    public function setArpAttributes($arpAttributes)
    {
        $this->arpAttributes = $arpAttributes;
    }

    /**
     * @return string
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
     * @param \Datetime $createdAtDate
     */
    public function setCreatedAtDate(\Datetime $createdAtDate)
    {
        $this->createdAtDate = $createdAtDate;;
    }

    /**
     * @param User $updatedByUser
     *
     */
    public function setUpdatedByUser(User $updatedByUser)
    {
        $this->updatedByUser = $updatedByUser;
    }

    /**
     * @Serializer\VirtualProperty
     */
    public function getUpdatedByUserId()
    {
        return $this->updatedByUser->getId();
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
     * @param NestedCollection $metadata
     */
    public function setMetadata(NestedCollection $metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * @return NestedCollection
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
     * @return array
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
     * @return array
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
     * @return array
     */
    public function getDisableConsentConnections()
    {
        return $this->disableConsentConnections;
    }
}
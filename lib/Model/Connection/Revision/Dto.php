<?php

use JMS\Serializer\Annotation AS Serializer;

class sspmod_janus_Model_Connection_Revision_Dto
{
    /**
     * @var sspmod_janus_Model_Connection
     *
     * @Serializer\Type("integer")
     */
    private $id;

    /**
     * @var sspmod_janus_Model_Connection
     */
    private $connection;

    /**
     * @var string
     *
     * @Serializer\Type("string")
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
     * Implemented only to show something descriptive on the connections overview
     *
     * @todo must be fixed a different way
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->name . ' (' . $this->id . ')';
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
     * @param \sspmod_janus_Model_Connection $connection
     */
    public function setConnection(\sspmod_janus_Model_Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return \sspmod_janus_Model_Connection
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
     * @return \DateTime
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

    public function setMetadataCacheUntil(\DateTime $metadataCacheUntil)
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

    public function setMetadataValidUntil(\DateTime $metadataValidUntil)
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
}
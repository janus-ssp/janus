<?php

class sspmod_janus_Model_Connection_Revision_Dto
{
    /**
     * @var sspmod_janus_Model_Connection
     */
    private $id;

    /**
     * @var sspmod_janus_Model_Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $revisionNr;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $type;

    /**
     * @var DateTime
     */
    private $expirationDate;

    /**
     * @var string
     */
    private $metadataUrl;

    /**
     */
    private $metadataValidUntil;

    /**
     */
    private $metadataCacheUntil;

    /**
     * @var bool
     */
    private $allowAllEntities;

    /**
     * @var string
     */
    private $arpAttributes;

    /**
     * @var string
     */
    private $manipulationCode;

    /**
     * @var int
     */
    private $parentRevisionNr;

    /**
     * @var string
     */
    private $revisionNote;

    /**
     * @var string
     */
    private $notes;
    /**
     * @var bool
     */
    private $isActive;

    /**
     * @param boolean $allowAllEntities
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
     * @param \DateTime $expirationDate
     */
    public function setExpirationDate($expirationDate)
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
     * @param \sspmod_janus_Model_Connection $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return \sspmod_janus_Model_Connection
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

    public function setMetadataCacheUntil($metadataCacheUntil)
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
<?php

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use JMS\Serializer\Annotation AS Serializer;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *  name="connection",
 *  indexes={
 *      @ORM\index(name="revisionNr",columns={"revisionNr"})
 *  },
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="unique_name_per_type", columns={"name", "type"})
 * }
 * )
 */
class sspmod_janus_Model_Connection
{
    const MAX_NAME_LENGTH = 255;
    const MAX_TYPE_LENGTH = 50;

    const TYPE_IDP = 'saml20-idp';
    const TYPE_SP = 'saml20-sp';

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     */
    protected $id;

    /**
     * @var int
     *
     * @ORM\Column(name="revisionNr", type="integer")
     */
    protected $revisionNr;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Serializer\Groups({"compare"})
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="type", length=50)
     * @Serializer\Groups({"compare"})
     */
    protected $type;

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
     * @var \Doctrine\ORM\PersistentCollection
     *
     * @ORM\OneToMany(targetEntity="sspmod_janus_Model_Connection_Revision", mappedBy="connection", cascade={"persist", "remove"})
     */
    protected $revisions;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="sspmod_janus_Model_User_ConnectionRelation", mappedBy="connection")
     */
    protected $userRelations;

    /**
     * @param string $name
     * @param string $type one of the TYPE_XXX constants
     */
    public function __construct(
        $name,
        $type
    )
    {
        $this->setName($name);
        $this->setType($type);

        // Create initial revision
        $dto = new sspmod_janus_Model_Connection_Revision_Dto();
        $dto->setName($name);
        $dto->setType($type);
        // @todo pass this as parameter
        $dto->setRevisionNote('initial revision');

        $this->createRevision($dto);
    }

    /**
     * Updates connection and stores versionable data in a new revision.
     *
     * @param string $name
     * @param string $type
     * @param string|null $parentRevisionNr
     * @param string $revisionNote
     * @param strin $state
     * @param DateTime $expirationDate
     * @param string|null $metadataUrl
     * @param bool $allowAllEntities
     * @param array $arpAttributes
     * @param string|null $manipulationCode
     * @param bool $isActive
     * @param string|null $notes
     * @return sspmod_janus_Model_Connection_Revision
     *
     * @todo split this in several smaller method like rename(), activate() etc.
     */
    public function update(
        $name,
        $type,
        $parentRevisionNr = null,
        $revisionNote,
        $state,
        \DateTime $expirationDate = null,
        $metadataUrl = null,
        $allowAllEntities = true,
        array $arpAttributes = null,
        $manipulationCode = null,
        $isActive = true,
        $notes = null
    )
    {
        // Update connection
        $this->setName($name);
        $this->setType($type);

        // Update revision
        $dto = $this->createDto();
        $dto->setName($name);
        $dto->setType($type);
        $dto->setParentRevisionNr($parentRevisionNr);
        $dto->setRevisionNote($revisionNote);
        $dto->setState($state);
        $dto->setExpirationDate($expirationDate);
        $dto->setMetadataUrl($metadataUrl);
        $dto->setAllowAllEntities($allowAllEntities);
        $dto->setArpAttributes($arpAttributes);
        $dto->setManipulationCode($manipulationCode);
        $dto->setIsActive($isActive);
        $dto->setNotes($notes);

        $this->createRevision($dto);
    }

    /**
     * Creates a Data transfer object based on either the current revision or a new one.
     *
     * @return sspmod_janus_Model_Connection_Revision_Dto
     */
    private function createDto()
    {
        $latestRevision = $this->getLatestRevision();
        if ($latestRevision instanceof sspmod_janus_Model_Connection_Revision) {
            return $latestRevision->toDto();
        } else {
            return new sspmod_janus_Model_Connection_Revision_Dto();
        }
    }

    /**
     * Creates a new revision.
     *
     * @param sspmod_janus_Model_Connection_Revision_Dto $dto
     * @return sspmod_janus_Model_Connection_Revision
     */
    private function createRevision(
        sspmod_janus_Model_Connection_Revision_Dto $dto
    )
    {
        $this->revisionNr = $this->getNewRevisionNr();
        $dto->setRevisionNr($this->revisionNr);

        // Create new revision
        $connectionRevision = new sspmod_janus_Model_Connection_Revision(
            $this,
            $dto->getRevisionNr(),
            $dto->getParentRevisionNr(),
            $dto->getRevisionNote(),
            $dto->getState(),
            $dto->getExpirationDate(),
            $dto->getMetadataUrl(),
            $dto->getAllowAllEntities(),
            $dto->getArpAttributes(),
            $dto->getManipulationCode(),
            $dto->getIsActive(),
            $dto->getNotes()
        );

        $this->setLatestRevision($connectionRevision);
    }

    /**
     * Generates a new revision nr starting by 0
     *
     * @return int
     */
    private function getNewRevisionNr()
    {
        $latestRevision = $this->getLatestRevision();
        if (!$latestRevision instanceof sspmod_janus_Model_Connection_Revision) {
            return 0;
        }

        $isRevisionAlreadyPersisted = (!is_null($latestRevision->getId()));

        if ($isRevisionAlreadyPersisted) {
            return $latestRevision->getRevisionNr() + 1;
        }

        return $latestRevision->getRevisionNr();
    }

    /**
     * @return mixed
     *
     * Do not use this method when performance is important since it used the entire collection
     */
    public function getLatestRevision()
    {
        if (empty($this->revisions)) {
            return;
        }

        return $this->revisions->last();
    }

    /**
     * Sets latest revision.
     *
     * Adds revision to collection using it's number as key so that the count of revisions does not increase when saving fails
     *
     * @param sspmod_janus_Model_Connection_Revision $connectionRevision
     */
    private function setLatestRevision(sspmod_janus_Model_Connection_Revision $connectionRevision)
    {
        if (is_null($this->revisions)) {
            $this->revisions = new ArrayCollection();
        }

        $this->revisions[$connectionRevision->getRevisionNr()] = $connectionRevision;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getRevisionNr()
    {
        return $this->revisionNr;
    }

    /**
     * @param string $name
     * @return $this
     * @throws Exception
     */
    private function setName($name)
    {
        if (!is_string($name)) {
            throw new Exception("Name must be a string, instead an '" . gettype($name) . "' was passed");
        }

        if (empty($name)) {
            throw new Exception('Name cannot be empty');
        }

        $length = strlen($name);
        if ($length > self::MAX_NAME_LENGTH) {
            throw new Exception('Name is ' . $length . ' chars long while only ' . self::MAX_NAME_LENGTH . ' chars are allowed');
        }

        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $type
     * @return $this
     * @throws InvalidArgumentException
     */
    private function setType($type)
    {
        $allowedTypes = array(self::TYPE_IDP, self::TYPE_SP);
        if (!in_array($type, $allowedTypes)) {
            throw new \InvalidArgumentException("Unknown connection type '{$type}'");
        }
        $this->type = $type;
        return $this;
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
}
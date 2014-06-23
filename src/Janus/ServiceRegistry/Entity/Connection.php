<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Entity;

use DateTime;
use Exception;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation AS Serializer;

use Janus\ServiceRegistry\Connection\ConnectionDto;
use Janus\ServiceRegistry\Entity\Connection\Revision;
use Janus\ServiceRegistry\Entity\User;
use Janus\ServiceRegistry\Value\Ip;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *  name="connection",
 *  indexes={
 *      @ORM\Index(name="revisionNr",columns={"revisionNr"})
 *  },
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="unique_name_per_type", columns={"name", "type"})
 * }
 * )
 * @UniqueEntity(fields={"name", "type"}, errorPath="name")
 */
class Connection
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
     * @var Datetime
     *
     * @ORM\Column(name="created", type="janusDateTime", nullable=true)
     */
    protected $createdAtDate;

    /**
     * @var Ip
     *
     * @ORM\Column(name="ip", type="janusIp", nullable=true)
     */
    protected $updatedFromIp;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Janus\ServiceRegistry\Entity\User")
     * @ORM\JoinColumn(name="user", referencedColumnName="uid", nullable=true)
     */
    protected $updatedByUser;

    /**
     * @var \Doctrine\ORM\PersistentCollection
     *
     * @ORM\OneToMany(targetEntity="Janus\ServiceRegistry\Entity\Connection\Revision", mappedBy="connection", cascade={"persist", "remove"})
     */
    protected $revisions;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Janus\ServiceRegistry\Entity\User\ConnectionRelation", mappedBy="connection")
     */
    protected $userRelations;

    /**
     * @param $name
     * @param $type One of the TYPE_XXX constants
     * @param string $revisionNote
     */
    public function __construct(
        $name,
        $type,
        $revisionNote = 'initial revision'
    )
    {
        $this->rename($name);
        $this->changeType($type);

        // Create initial revision
        $dto = new ConnectionDto();
        $dto->setName($name);
        $dto->setType($type);
        $dto->setRevisionNote($revisionNote);

        $this->createRevision($dto);
    }

    /**
     * Updates connection and stores versionable data in a new revision.
     *
     * @param $name
     * @param $type
     * @param null $parentRevisionNr
     * @param $revisionNote
     * @param $state
     * @param DateTime $expirationDate
     * @param null $metadataUrl
     * @param bool $allowAllEntities
     * @param array $arpAttributes
     * @param null $manipulationCode
     * @param bool $isActive
     * @param null $notes
     * @param \SimpleSAML_Configuration $janusConfig
     *
     * @todo split this in several smaller method like rename(), activate() etc.
     */
    public function update(
        \SimpleSAML_Configuration $janusConfig,
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
        $this->rename($name);
        $this->changeType($type);

        // Update revision
        $dto = $this->createDto($janusConfig);
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
     * @return ConnectionDto
     */
    public function createDto(\SimpleSAML_Configuration $janusConfig)
    {
        $latestRevision = $this->getLatestRevision();
        if ($latestRevision instanceof Revision) {
            return $latestRevision->toDto($janusConfig);
        } else {
            return new ConnectionDto();
        }
    }

    /**
     * Creates a new revision.
     *
     * @param ConnectionDto $dto
     * @return Revision
     */
    private function createRevision(
        ConnectionDto $dto
    )
    {
        $this->revisionNr = $this->getNewRevisionNr();
        $dto->setRevisionNr($this->revisionNr);

        // Create new revision
        $connectionRevision = new Revision(
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
        if (!$latestRevision instanceof Revision) {
            return 0;
        }

        $isRevisionAlreadyPersisted = (!is_null($latestRevision->getId()));

        if ($isRevisionAlreadyPersisted) {
            return $latestRevision->getRevisionNr() + 1;
        }

        return $latestRevision->getRevisionNr();
    }

    /**
     * Do not use this method when performance is important since it used the entire collection
     *
     * @return Revision
     */
    public function getLatestRevision()
    {
        if (empty($this->revisions)) {
            return null;
        }

        return $this->revisions->last();
    }

    /**
     * Sets latest revision.
     *
     * Adds revision to collection using it's number as key so that the count of revisions does not increase when saving fails
     *
     * @param Revision  $connectionRevision
     */
    private function setLatestRevision(Revision $connectionRevision)
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
    public function rename($name)
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
     * @param $type
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function changeType($type)
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
     * @return \DateTime
     */
    public function getCreatedAtDate()
    {
        return $this->createdAtDate;
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
     * @param User $updatedByUser
     * @return $this
     */
    public function setUpdatedByUser(User $updatedByUser)
    {
        $this->updatedByUser = $updatedByUser;
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

    public function schedule(\DateTime $time = null)
    {
        if (is_null($time)) {
            $time = new DateTime();
        }
    }
}

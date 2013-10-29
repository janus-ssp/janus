<?php

use Doctrine\ORM\Mapping AS ORM;
use sspmod_janus_Model_Entity_Id as Id;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *  name="entity"
 * )
 */
class sspmod_janus_Model_Entity
{
    const TYPE_IDP = 'idp';
    const TYPE_SP = 'sp';

    /**
     * @var sspmod_janus_Model_Entity_Id
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="sspmod_janus_Model_Entity_Id")
     * @ORM\JoinColumn(name="eid", referencedColumnName="eid")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="entityid", type="text")
     *
     */
    protected $entityId;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="revisionid", type="integer")
     */
    protected $revisionNr;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="text", nullable=true)
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
    protected $expiration;

    /**
     * @var string
     *
     * @ORM\Column(name="metadataurl", type="text", nullable=true)
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
     */
    protected $allowAllEntities = true;

    /**
     * @var sspmod_janus_Model_Entity_Arp
     *
     * @ORM\ManyToOne(targetEntity="sspmod_janus_Model_Entity_Arp")
     * @ORM\JoinColumn(name="arp", referencedColumnName="aid", nullable=true)
     */
    protected $arp;

    /**
     * @var text
     *
     * @ORM\Column(name="manipulation", type="text", columnDefinition="mediumtext", nullable=true)
     *
     * @todo Get rid of column definition that is just here to make models match to current db structure
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
     */
    protected $isActive = true;

    /**
     * @var array
     *
     * @ORM\OneToMany(targetEntity="sspmod_janus_Model_Entity_Metadata", mappedBy="entity", fetch="LAZY")
     */
    protected $metadata;

    /**
     * @param sspmod_janus_Model_Entity_Id $id
     * @param string $type on of the TYPE_XXX constants
     * @throws Exception
     */
    public function __construct(
        Id $id,
        $type
    ) {
        $this->id = $id->getid();

        // @todo move to method
        $allowedTypes = array(self::TYPE_IDP, self::TYPE_SP);
        if (!in_array($type, $allowedTypes)) {
            throw new Exception ("Unknown entity type '{$type}'");
        }

        $this->type = $type;
        $this->id = $id;
        $this->entityId = $id->getEntityid();
        $this->revisionNr = 0;
        $this->createdAtDate = new \DateTime();
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
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @return int
     */
    public function getRevisionNr()
    {
        return $this->revisionNr;
    }
}
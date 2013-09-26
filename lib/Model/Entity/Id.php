<?php

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *  name="entityId",
 *  uniqueConstraints={@ORM\UniqueConstraint(name="entityid", columns={"entityid"})})
 */
class sspmod_janus_Model_Entity_Id
{
    const MAX_ENTITYID_LENGTH = 255;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="eid", type="integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="entityid", type="string", length=255)
     */
    protected $entityid;

    /**
     * @param string $entityid
     */
    public function __construct(
         $entityid
    )
    {
        $this->setEntityid($entityid);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $entityid
     * @return $this
     * @throws Exception
     */
    public function setEntityid($entityid)
    {
        if (!is_string($entityid)) {
            throw new Exception("Entityid must be a string, instead an '" .  gettype($entityid) . "' was passed");
        }

        if (empty($entityid)) {
            throw new Exception('Entityid cannot be empty');
        }

        $length = strlen($entityid);
        if ($length > self::MAX_ENTITYID_LENGTH) {
            throw new Exception('Entityid is ' . $length . ' chars long while only ' . self::MAX_ENTITYID_LENGTH . ' chars are allowed');
        }

        $this->entityid = $entityid;
        return $this;
    }

    /**
     * @return string
     */
    public function getEntityid()
    {
        return $this->entityid;
    }
}
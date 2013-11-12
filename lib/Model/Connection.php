<?php

use Doctrine\ORM\Mapping AS ORM;
use JMS\Serializer\Annotation AS Serializer;


/**
 * @ORM\Entity()
 * @ORM\Table(
 *  name="entity",
 *  uniqueConstraints={@ORM\UniqueConstraint(name="entityid", columns={"entityid"})})
 *
 */
class sspmod_janus_Model_Connection
{
    const MAX_NAME_LENGTH = 255;

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
     * @Serializer\Groups({"compare"})
     */
    protected $name;

    /**
     * @param string $name
     */
    public function __construct(
         $name
    )
    {
        $this->setName($name);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     * @return $this
     * @throws Exception
     */
    public function setName($name)
    {
        if (!is_string($name)) {
            throw new Exception("Name must be a string, instead an '" .  gettype($name) . "' was passed");
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
}
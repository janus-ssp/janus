<?php

use Doctrine\ORM\Mapping AS ORM;
use JMS\Serializer\Annotation AS Serializer;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *  name="connection",
 *  uniqueConstraints={@ORM\UniqueConstraint(name="unique_name_per_type", columns={"entityid", "type"})})
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Serializer\Groups({"compare"})
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="text", length=50)
     * @Serializer\Groups({"compare"})
     */
    protected $type;

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
    }

    /**
     * @param string $name
     * @param string $type one of the TYPE_XXX constants
     */
    public function update(
        $name,
        $type
    )
    {
        $this->setName($name);
        $this->setType($type);
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
    private function setName($name)
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

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
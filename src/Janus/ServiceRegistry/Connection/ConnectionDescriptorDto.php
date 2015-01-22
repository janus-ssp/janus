<?php

namespace Janus\ServiceRegistry\Connection;

use DateTime;

use JMS\Serializer\Annotation AS Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class ConnectionDescriptorDto
{
    /**
     * Unique Identifier
     *
     * @var integer
     *
     * @Serializer\Type("integer")
     * @Serializer\ReadOnly
     */
    public $id;

    /**
     * The connection itself, not serialized.
     *
     * @var Connection
     */
    public $connection;

    /**
     * Name (or in SAML speak 'entityid')
     *
     * @var string
     *
     * @Serializer\Type("string")
     * @Assert\Length(max="255")
     * @Assert\NotNull
     */
    public $name;

    /**
     * Revision number
     *
     * @var int
     *
     * @Serializer\Type("integer")
     * @Serializer\ReadOnly
     */
    public $revisionNr;

    /**
     * State (e.g. testaccepted, prodaccepted)
     *
     * @var string
     *
     * @Serializer\Type("string")
     * @Assert\Length(max="255")
     * @Assert\NotNull
     */
    public $state;

    /**
     * Type (e.g. saml20-sp, saml20-idp)
     *
     * @var string
     *
     * @Serializer\Type("string")
     * @Assert\Length(max="255")
     * @Assert\NotNull
     */
    public $type;

    /**
     * Date / time the connection itself can be considered as being expired
     *
     * @var \DateTime
     *
     * @Serializer\Type("DateTime")
     * @Assert\DateTime()
     */
    public $expirationDate;

    /**
     * General note
     *
     * @var string
     *
     * @Serializer\Type("string")
     * @Assert\Length(max="65536")
     */
    public $notes;


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
}

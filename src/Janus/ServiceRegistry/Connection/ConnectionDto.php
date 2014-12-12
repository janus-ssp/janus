<?php

namespace Janus\ServiceRegistry\Connection;

use DateTime;

use JMS\Serializer\Annotation AS Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class ConnectionDto
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
     * Url to the metadata
     *
     * @var string
     *
     * @Serializer\Type("string")
     * @Assert\Length(max="255")
     */
    public $metadataUrl;

    /**
     * Date / time until the metadata can be considered as valid
     *
     * @var string
     *
     * @Serializer\Type("string")
     * @Assert\DateTime()
     */
    public $metadataValidUntil;

    /**
     * Date / time until when the metadata can be safely cached
     *
     * @var \Datetime
     *
     * @Assert\DateTime()
     * @Serializer\Type("DateTime")
     */
    public $metadataCacheUntil;

    /**
     * Are all connections allowed to connection to this connection?
     *
     * @var bool
     *
     * @Serializer\Type("boolean")
     */
    public $allowAllEntities;

    /**
     * A list of attributes that will be will released to the Service Provider (Identity Provider only)
     *
     * @var array
     *
     * @Serializer\Type("array<string, array>")
     */
    public $arpAttributes = null;

    /**
     * PHP code which can be used to manipulate a request
     *
     * @var string
     *
     * @Serializer\Type("string")
     * @Assert\Length(max="65536")
     */
    public $manipulationCode;

    /**
     * Number of the Revision this revision was based on
     *
     * @var int
     *
     * @Serializer\Type("integer")
     * @Serializer\ReadOnly
     */
    public $parentRevisionNr;

    /**
     * Note regarding this specific revision
     *
     * @var string
     *
     * @Serializer\Type("string")
     * @Assert\Length(max="65536")
     * @Assert\NotNull
     */
    public $revisionNote;

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
     * Is the connection active?
     *
     * @var bool
     *
     * @Serializer\Type("boolean")
     */
    public $isActive;

    /**
     * User that made last update
     *
     * @var string
     */
    public $updatedByUserName;

    /**
     * Date / time of creation
     *
     * @var \DateTime
     *
     * @Serializer\Type("DateTime")
     * @Serializer\ReadOnly
     */
    public $createdAtDate;

    /**
     * Date / time of last update
     *
     * @var \Datetime
     *
     * @Serializer\Type("DateTime")
     * @Serializer\ReadOnly
     */
    public $updatedAtDate;

    /**
     * Ip from which last update took place
     *
     * @var string
     */
    public $updatedFromIp;

    /**
     * Nested metadata
     *
     * @var array
     *
     * @Serializer\Type("array<array>")
     */
    public $metadata;

    /**
     * Connection that are allowed to connect
     *
     * @var array
     *
     * @Serializer\Type("array")
     */
    public $allowedConnections = array();

    /**
     * Connections that are NOT allowed to connect
     *
     * @var array
     *
     * @Serializer\Type("array")
     */
    public $blockedConnections = array();

    /**
     * Connections for which no consent is required when connecting
     *
     * @var array
     *
     * @Serializer\Type("array")
     */
    public $disableConsentConnections = array();

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

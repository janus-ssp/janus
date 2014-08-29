<?php

namespace Janus\ServiceRegistry\Entity\Connection;

use DateTime;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\ORM\PersistentCollection;
use Janus\Component\ReadonlyEntities\Entities\Connection\Revision\AllowedConnectionRelation;
use Janus\Component\ReadonlyEntities\Entities\Connection\Revision\BlockedConnectionRelation;
use Janus\Component\ReadonlyEntities\Entities\Connection\Revision\DisableConsentRelation;
use Janus\Component\ReadonlyEntities\Entities\Connection\Revision\Metadata;
use Janus\ServiceRegistry\Connection\Metadata\MetadataDefinitionHelper;
use Janus\ServiceRegistry\Connection\Metadata\MetadataDto;
use JMS\Serializer\Annotation AS Serializer;

use Janus\Component\ReadonlyEntities\Connection;
use Janus\ServiceRegistry\Connection\ConnectionDto;
use Janus\Component\ReadonlyEntities\Value\Ip;
use \Janus\Component\ReadonlyEntities\Entities\Connection\Revision as ReadonlyRevision;

/**
 * @ORM\Entity(
 *  repositoryClass="Janus\ServiceRegistry\Entity\Connection\RevisionRepository"
 * )
 * @ORM\Table(
 *  name="connectionRevision",
 *  uniqueConstraints={@ORM\UniqueConstraint(name="unique_revision",columns={"eid", "revisionid"})}
 * )
 */
class Revision extends ReadonlyRevision
{
    /**
     * @var \Janus\ServiceRegistry\Entity\Connection
     *
     * @ORM\ManyToOne(targetEntity="Janus\ServiceRegistry\Entity\Connection", inversedBy="revisions")
     * @ORM\JoinColumn(name="eid", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Serializer\Groups({"compare"})
     */
    protected $connection;

   /**
     * @var \Janus\ServiceRegistry\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Janus\ServiceRegistry\Entity\User")
     * @ORM\JoinColumn(name="user", referencedColumnName="uid", nullable=true)
     */
    protected $updatedByUser;

    /**
     * Creates a ConnectionDto that can be used to clone a revision
     *
     * @todo move this to an Assembler
     *
     * @param $janusConfig
     * @return ConnectionDto
     */
    public function toDto($janusConfig)
    {
        $dto = new ConnectionDto();
        $dto->setId($this->connection->getId());
        $dto->setName($this->name);
        $dto->setType($this->type);
        $dto->setRevisionNr($this->revisionNr);
        $dto->setParentRevisionNr($this->parentRevisionNr);
        $dto->setRevisionNote($this->revisionNote);
        $dto->setState($this->state);
        $dto->setExpirationDate($this->expirationDate);
        $dto->setMetadataUrl($this->metadataUrl);
        $dto->setAllowAllEntities($this->allowAllEntities);
        $dto->setArpAttributes($this->arpAttributes);
        $dto->setManipulationCode($this->manipulationCode);
        $dto->setIsActive($this->isActive);
        $dto->setNotes($this->notes);

        $setAuditProperties = !empty($this->id);
        if ($setAuditProperties) {
            $dto->setCreatedAtDate($this->connection->getCreatedAtDate());
            $dto->setUpdatedAtDate($this->createdAtDate);
            $dto->setUpdatedByUser($this->updatedByUser);
            $dto->setUpdatedFromIp($this->updatedFromIp);
        }

        if ($this->metadata instanceof PersistentCollection) {
            $flatMetadata = array();
            /** @var $metadataRecord Metadata */
            foreach ($this->metadata as $metadataRecord) {
                $flatMetadata[$metadataRecord->getKey()] = $metadataRecord->getValue();
            }

            if (!empty($flatMetadata)) {
                $metadataCollection = MetadataDto::createFromFlatArray(
                    $flatMetadata,
                    new MetadataDefinitionHelper($this->type, $janusConfig)
                );
                $dto->setMetadata($metadataCollection);
            }
        }

        if ($this->allowedConnectionRelations instanceof PersistentCollection) {

            $allowedConnections = array();
            /** @var $relation AllowedConnectionRelation */
            foreach ($this->allowedConnectionRelations as $relation) {
                $remoteConnection = $relation->getRemoteConnection();
                $allowedConnections[] = array(
                    'id' => $remoteConnection->getId(),
                    'name' => $remoteConnection->getName()
                );
            }
            $dto->setAllowedConnections($allowedConnections);
        }

        if ($this->blockedConnectionRelations instanceof PersistentCollection) {
            $blockedConnections = array();
            /** @var $relation BlockedConnectionRelation */
            foreach ($this->blockedConnectionRelations as $relation) {
                $remoteConnection = $relation->getRemoteConnection();
                $blockedConnections[] = array(
                    'id' => $remoteConnection->getId(),
                    'name' => $remoteConnection->getName()
                );
            }
            $dto->setBlockedConnections($blockedConnections);
        }

        if ($this->disableConsentConnectionRelations instanceof PersistentCollection) {
            $disableConsentConnections = array();
            /** @var $relation DisableConsentRelation */
            foreach ($this->disableConsentConnectionRelations as $relation) {
                $remoteConnection = $relation->getRemoteConnection();
                $disableConsentConnections[] = array(
                    'id' => $remoteConnection->getId(),
                    'name' => $remoteConnection->getName()
                );
            }
            $dto->setDisableConsentConnections($disableConsentConnections);
        }

        return $dto;
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
     * @param \Janus\ServiceRegistry\Entity\User $updatedByUser
     * @return $this
     */
    public function setUpdatedByUser(\Janus\ServiceRegistry\Entity\User $updatedByUser)
    {
        $this->updatedByUser = $updatedByUser;
        return $this;
    }

    /**
     * @param \Janus\Component\ReadonlyEntities\Value\Ip $updatedFromIp
     * @return $this
     */
    public function setUpdatedFromIp(Ip $updatedFromIp)
    {
        $this->updatedFromIp = $updatedFromIp;
        return $this;
    }

    public function allowConnection($connection)
    {
        $this->allowedConnectionRelations[] = new AllowedConnectionRelation(
            $this,
            $connection
        );
        return $this;
    }

    public function blockConnection($connection)
    {
        $this->blockedConnectionRelations[] = new BlockedConnectionRelation(
            $this,
            $connection
        );
        return $this;
    }

    public function disableConsentForConnection($connection)
    {
        $this->disableConsentConnectionRelations[] = new DisableConsentRelation(
            $this,
            $connection
        );
        return $this;
    }
}

<?php

namespace Janus\ServiceRegistry\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation AS Serializer;

use Janus\ServiceRegistry\ConfigProxy;
use Janus\ServiceRegistry\Connection\ConnectionDto;
use Janus\ServiceRegistry\Entity\Connection\Revision;

use Janus\Component\ReadonlyEntities\Value\Ip;
use Janus\Component\ReadonlyEntities\Entities\Connection as ReadonlyConnection;

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
class Connection extends ReadonlyConnection
{
    /**
     * Updates connection and stores versionable data in a new revision.
     *
     * @param $name
     * @param $type
     * @param null $parentRevisionNr
     * @param $revisionNote
     * @param $state
     * @param \DateTime $expirationDate
     * @param null $metadataUrl
     * @param bool $allowAllEntities
     * @param array $arpAttributes
     * @param null $manipulationCode
     * @param bool $isActive
     * @param null $notes
     * @param \Janus\ServiceRegistry\ConfigProxy $janusConfig
     *
     * @todo split this in several smaller method like rename(), activate() etc.
     */
    public function update(
        ConfigProxy $janusConfig,
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
     * @param \Janus\ServiceRegistry\ConfigProxy $janusConfig
     * @return ConnectionDto
     */
    public function createDto(ConfigProxy $janusConfig)
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
     * @param \DateTime $createdAtDate
     * @return $this
     */
    public function setCreatedAtDate(\DateTime $createdAtDate)
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
     * @param \Janus\Component\ReadonlyEntities\Value\Ip $updatedFromIp
     * @return $this
     */
    public function setUpdatedFromIp(Ip $updatedFromIp)
    {
        $this->updatedFromIp = $updatedFromIp;
        return $this;
    }
}

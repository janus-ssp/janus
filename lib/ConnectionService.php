<?php
use Doctrine\ORM\EntityManager;

/**
 * Service layer for all kinds of entity related logic
 *
 * Class sspmod_janus_ConnectionService
 */
class sspmod_janus_ConnectionService extends sspmod_janus_Database
{

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * JANUS configuration
     * @var SimpleSAML_Configuration
     */
    private $config;

    /**
     * @param EntityManager $entityManager
     * @param SimpleSAML_Configuration $config
     */
    public function __construct(EntityManager $entityManager, SimpleSAML_Configuration $config)
    {
        $this->entityManager = $entityManager;
        $this->config = $config;
        parent::__construct($config->getValue('store'));
    }

    /**
     * Grants a user permission to a given entity
     *
     * @param sspmod_janus_Model_Connection $entity
     * @param sspmod_janus_Model_User $user
     */
    public function addUserPermission(sspmod_janus_Model_Connection $connection, sspmod_janus_Model_User $user)
    {
        $userConnectionRelation = new sspmod_janus_Model_User_ConnectionRelation(
            $user,
            $connection
        );

        $this->entityManager->persist($userConnectionRelation);
        $this->entityManager->flush();
    }
}

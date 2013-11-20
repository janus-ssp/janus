<?php
use Doctrine\ORM\EntityManager;

/**
 * Service layer for all kinds of user related logic
 *
 * Class sspmod_janus_UserService
 */
class sspmod_janus_UserService extends sspmod_janus_Database
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
     * @param int $id
     * @return sspmod_janus_Model_User
     * @throws Exception
     */
    public function getById($id)
    {
        $user = $this->entityManager->getRepository('sspmod_janus_Model_User')->find($id);
        if (!$user instanceof sspmod_janus_Model_User) {
            throw new \Exception("User '{$id}' not found");
        }

        return $user;
    }
}

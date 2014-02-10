<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Service;

use Exception;

use Doctrine\ORM\EntityManager;

use SimpleSAML_Configuration;

use Janus\ServiceRegistry\Entity\User;

/**
 * Service layer for all kinds of user related logic
 *
 * Class Janus\ServiceRegistry\Service\UserService
 */
class UserService
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
    }

    /**
     * @param int $id
     * @return User
     * @throws Exception
     */
    public function getById($id)
    {
        $user = $this->entityManager->getRepository('Janus\ServiceRegistry\Entity\User')->find($id);
        if (!$user instanceof User) {
            throw new Exception("User '{$id}' not found");
        }

        return $user;
    }
}

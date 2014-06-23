<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Service;

use Exception;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

use SimpleSAML_Configuration;

use Janus\ServiceRegistry\Entity\User;

/**
 * Service layer for all kinds of user related logic
 *
 * Class Janus\ServiceRegistry\Service\UserService
 */
class UserService implements UserProviderInterface
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
    public function findById($id)
    {
        $user = $this->entityManager->find('Janus\ServiceRegistry\Entity\User', $id);

        if (!$user instanceof User) {
            throw new Exception("User '{$id}' not found");
        }

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function loadUserByUsername($username)
    {
        $users = $this->entityManager->getRepository('Janus\ServiceRegistry\Entity\User')->findBy(array(
            'username' => $username
        ));

        if (empty($users)) {
            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $username)
            );
        }
        if (count($users) > 1) {
            throw new UsernameNotFoundException(
                sprintf('Multiple candidate users for username "%s".', $username)
            );
        }
        $user = $users[0];
        if (!$user instanceof UserInterface) {
            throw new UsernameNotFoundException(
                sprintf('User found for "%s" is not a valid user.', $username)
            );
        }

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * @inheritDoc
     */
    public function supportsClass($class)
    {
        return $class === 'Janus\ServiceRegistry\Entity\User';
    }
}

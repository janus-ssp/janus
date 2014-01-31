<?php
namespace Janus\ServiceRegistry\Doctrine\Listener;

use Exception;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;

use DateTime;

use Janus\ServiceRegistry\DependencyInjection\AuthProviderInterface;
use Janus\ServiceRegistry\DependencyInjection\TimeProvider;
use Janus\ServiceRegistry\Entity\User;
use Janus\ServiceRegistry\Value\Ip;

class AuditPropertiesUpdater
{
    const DEFAULT_IP = '127.0.0.1';

    /**
     * @var AuthProviderInterface
     */
    private $authProvider;

    /**
     * @var TimeProvider
     */
    private $timeProvider;

    /**
     * @param AuthProviderInterface $authProvider
     * @param TimeProvider $timeProvider
     */
    public function __construct(
        AuthProviderInterface $authProvider,
        TimeProvider $timeProvider
    )
    {
        $this->authProvider = $authProvider;
        $this->timeProvider = $timeProvider;
    }

    /**
     * Executes on every flush. All entities that are scheduled for persistence can be changed here.
     * @param \Doctrine\ORM\Event\OnFlushEventArgs $eventArgs
     */
    public function onFlush(\Doctrine\ORM\Event\OnFlushEventArgs $eventArgs)
    {
        $entityManager = $eventArgs->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $userIp = new Ip($_SERVER['REMOTE_ADDR']);
        } else {
            $userIp = new Ip(self::DEFAULT_IP);
        }
        $authProvider = $this->authProvider;
        $updater = $this;
        $loggedInUser = function () use ($updater, $authProvider, $entityManager) {
            return $updater->getLoggedInUser($entityManager, $authProvider);
        };

        $time = $this->timeProvider->getDateTime();
        $methods = array(
            'setCreatedAtDate' => array(
                'insertValue' => $time,
            ),
            'setUpdatedAtDate' => array(
                'updateValue' => $time,
            ),
            // @todo fix that deleted date accepts null values
            'setUpdatedByUser' => array(
                'insertValue' => $loggedInUser,
                'updateValue' => $loggedInUser
            ),
            'setUpdatedFromIp' => array(
                'insertValue' => $userIp,
                'updateValue' => $userIp
            )
        );

        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            $class = get_class($entity);
            foreach ($methods as $method => $values) {
                if (isset($values['insertValue']) && method_exists($entity, $method)) {
                    $value = is_callable($values['insertValue']) ? $values['insertValue']() : $values['insertValue'];
                    $entity->$method($value);
                }
            }

            // needed to save the changed date value
            $unitOfWork->recomputeSingleEntityChangeSet($entityManager->getClassMetadata($class), $entity);
            $entityManager->persist($entity);
        }

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            $class = get_class($entity);
            foreach ($methods as $method => $values) {
                if (isset($values['updateValue']) && method_exists($entity, $method)) {
                    $value = is_callable($values['updateValue']) ? $values['updateValue']() : $values['updateValue'];
                    $entity->$method($value);
                }
            }

            // needed to save the changed date value
            $unitOfWork->recomputeSingleEntityChangeSet($entityManager->getClassMetadata($class), $entity);
            $entityManager->persist($entity);
        }
    }

    /**
     * @param EntityManager $entityManager
     * @param AuthProviderInterface $authProvider
     * @return User
     * @throws \Exception
     */
    private function getLoggedInUser(EntityManager $entityManager, AuthProviderInterface $authProvider)
    {
        $username = $authProvider->getLoggedInUsername();
        $user = $entityManager->getRepository('Janus\ServiceRegistry\Entity\User')
            ->findOneBy(array('username' => $username));

        if (!$user instanceof User) {
            throw new Exception("No User logged in");
        }

        return $user;
    }
}

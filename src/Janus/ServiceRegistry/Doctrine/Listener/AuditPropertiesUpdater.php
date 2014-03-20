<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Doctrine\Listener;

use Exception;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;

use DateTime;

use Janus\ServiceRegistry\DependencyInjection\AuthenticationProviderInterface;
use Janus\ServiceRegistry\DependencyInjection\TimeProvider;
use Janus\ServiceRegistry\Entity\User;
use Janus\ServiceRegistry\Value\Ip;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\SecurityContext;

class AuditPropertiesUpdater extends ContainerAware
{
    const DEFAULT_IP = '127.0.0.1';

    /**
     * @var SecurityContext
     */
    private $securityContext;

    /**
     * @var TimeProvider
     */
    private $timeProvider;

    /**
     * @param SecurityContext $securityContext
     * @param TimeProvider $timeProvider
     */
    public function __construct(
        TimeProvider $timeProvider
    ) {
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

        /** @var SecurityContext $securityContext */
        $securityContext = $this->container->get('security.context');
        if (!$securityContext) {
            throw new \RuntimeException('No Security Context set yet!');
        }
        $token = $securityContext->getToken();
        $loggedInUser = function () use ($token) {
            $user = $token->getUser();
            if (!$token->isAuthenticated() || !$user instanceof User) {
                throw new \RuntimeException('No User logged in');
            }
            return $user;
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
            ),
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
}

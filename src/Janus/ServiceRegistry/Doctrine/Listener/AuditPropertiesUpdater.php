<?php
namespace Janus\ServiceRegistry\Doctrine\Listener;

use Doctrine\ORM\Event\OnFlushEventArgs;

use DateTime;

use sspmod_janus_DiContainer;
use Janus\ServiceRegistry\Value\Ip;


class AuditPropertiesUpdater
{
    const DEFAULT_IP = '127.0.0.1';

    /**
     * @var sspmod_janus_DiContainer
     */
    private $diContainer;

    public function __construct(sspmod_janus_DiContainer $diContainer)
    {
        $this->diContainer = $diContainer;
    }

    /**
     * Executes on every flush. All entities that are scheduled for persistence can be changed here.
     * @param \Doctrine\ORM\Event\OnFlushEventArgs $eventArgs
     */
    public function onFlush(\Doctrine\ORM\Event\OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $userIp = new Ip($_SERVER['REMOTE_ADDR']);
        } else {
            $userIp = new Ip(self::DEFAULT_IP);
        }
        $diContainer = $this->diContainer;
        $loggedInUser = function() use ($diContainer) {
            return $loggedInUser = $diContainer->getLoggedInUser();
        };
        $methods = array(
            'setCreatedAtDate' => array(
                'insertValue' => new DateTime(),
            ),
            'setUpdatedAtDate' => array(
                'updateValue' => new DateTime(),
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

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $class= get_class($entity);
            foreach($methods as $method => $values) {
                if (isset($values['insertValue']) && method_exists($entity, $method)) {
                    $value = is_callable($values['insertValue']) ? $values['insertValue']() : $values['insertValue'];
                    $entity->$method($value);
                }
            }

            // needed to save the changed date value
            $uow->recomputeSingleEntityChangeSet($em->getClassMetadata($class), $entity);
            $em->persist($entity);
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $class= get_class($entity);
            foreach($methods as $method => $values) {
                if (isset($values['updateValue']) && method_exists($entity, $method)) {
                    $value = is_callable($values['updateValue']) ? $values['updateValue']() : $values['updateValue'];
                    $entity->$method($value);
                }
            }

            // needed to save the changed date value
            $uow->recomputeSingleEntityChangeSet($em->getClassMetadata($class), $entity);
            $em->persist($entity);
        }
    }
}

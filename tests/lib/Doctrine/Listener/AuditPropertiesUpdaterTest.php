<?php

use \Doctrine\ORM\Event\OnFlushEventArgs;

class sspmod_janus_Doctrine_Listener_AuditPropertiesUpdaterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var sspmod_janus_Doctrine_Listener_AuditPropertiesUpdater
     */
    private $auditPropertiesUpdater;

    /**
     * @var sspmod_janus_DiContainer
     */
    private $diContainer;

    public function setUp()
    {
        $this->diContainer = \Phake::mock('sspmod_janus_DiContainer');
        $this->auditPropertiesUpdater = new sspmod_janus_Doctrine_Listener_AuditPropertiesUpdater($this->diContainer);
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf('sspmod_janus_Doctrine_Listener_AuditPropertiesUpdater', $this->auditPropertiesUpdater);
    }

    public function testCreatedAtDateIsSetOnInsert()
    {
        $user = new sspmod_janus_Model_User('testUserName', array('admin'));
        Phake::when($this->diContainer)->getLoggedInUser()->thenReturn($user);

        $entityManager = \Phake::mock('\Doctrine\ORM\EntityManager');
        $unitOfWork = \Phake::mock('Doctrine\ORM\UnitOfWork');
        $testConnection = \Phake::mock('sspmod_janus_Model_Connection');
        $scheduledInsertions = array(
            $testConnection
        );
        Phake::when($unitOfWork)->getScheduledEntityInsertions()->thenReturn($scheduledInsertions);
        Phake::when($entityManager)->getUnitOfWork()->thenReturn($unitOfWork);
        $eventArgs = new OnFlushEventArgs($entityManager);
        $this->auditPropertiesUpdater->onFlush($eventArgs);
    }
}
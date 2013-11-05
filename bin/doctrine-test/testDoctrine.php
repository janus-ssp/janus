<?php
/**
 * Tests if all Doctrine models can be stored in the database properly
 *
 * NOTE: before running this, change your database name to a TEST database
 */

require_once __DIR__ . "/../../cli-config.php";
$user = new sspmod_janus_Model_User('admin', array('admin'));
$em->persist($user);
$user2 = new sspmod_janus_Model_User('test', array('technical'));
$em->persist($user2);
$em->flush();

$userData = new sspmod_janus_Model_User_Data($user, 'testKey', 'testValue');
$em->persist($userData);
$em->remove($userData);
$em->flush();

$userMessage = new sspmod_janus_Model_User_Message($user, 'testSubject', $user2, 'testSubscription');
$em->persist($userMessage);
$em->flush();
$em->remove($userMessage);
$em->flush();

$userSubscription = new sspmod_janus_Model_User_Subscription($user, 'testSubscription');
$em->persist($userSubscription);
$em->flush();
$em->remove($userSubscription);
$em->flush();

$entityArp = new sspmod_janus_Model_Entity_Revision_Arp(
    'testName',
    'testDescription',
    true,
    array('testAttribute')
);
$em->persist($entityArp);
$em->flush();

$entity = new sspmod_janus_Model_Entity('test-idp' . time());
$em->persist($entity);
$em->flush();

$entityRevision = new sspmod_janus_Model_Entity_Revision(
    $entity,
    0,
    null,
    'initial',
    'saml20-sp',
    'test',
    new \DateTime(),
    'http://test',
    true,
    $entityArp,
    null,
    true
);

$em->persist($entityRevision);
$em->flush();

$remoteEntity = new sspmod_janus_Model_Entity('test-sp' . time());
$em->persist($remoteEntity);
$em->flush();

$remoteEntityRevision = new sspmod_janus_Model_Entity_Revision(
    $remoteEntity,
    0,
    null,
    'initial',
    'saml20-sp',
    'test',
    new \DateTime(),
    'http://test',
    true,
    null,
    null,
    true
);
$em->persist($remoteEntityRevision);
$em->flush();

$entityAllowedEntityRelation = new sspmod_janus_Model_Entity_Revision_AllowedEntityRelation($entityRevision, $remoteEntity);
$em->persist($entityAllowedEntityRelation);
$em->flush();
$em->remove($entityAllowedEntityRelation);
$em->flush();

$entityBlockedEntityRelation = new sspmod_janus_Model_Entity_Revision_BlockedEntityRelation($entityRevision, $remoteEntity);
$em->persist($entityBlockedEntityRelation);
$em->flush();
$em->remove($entityBlockedEntityRelation);
$em->flush();

$entityDisableConsentRelation = new sspmod_janus_Model_Entity_Revision_DisableConsentRelation($entityRevision, $remoteEntity);
$em->persist($entityDisableConsentRelation);
$em->flush();
$em->remove($entityDisableConsentRelation);
$em->flush();

$entityMetadata = new sspmod_janus_Model_Entity_Revision_Metadata($entityRevision, 'testKey', 'testValue');
$em->persist($entityMetadata);
$em->flush();
$em->remove($entityMetadata);
$em->flush();

$em->remove($remoteEntityRevision);
$em->remove($remoteEntity);
$em->flush();

$userEntityRelation = new sspmod_janus_Model_User_EntityRelation($user, $entity);
$em->persist($userEntityRelation);
$em->flush();
$em->remove($userEntityRelation);
$em->flush();

$em->remove($user);
$em->remove($entityRevision);
$em->remove($entity);
$em->flush();

$em->remove($entityArp);
$em->flush();

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

$entityId = new sspmod_janus_Model_Entity_Id('test-idp' . time());
$em->persist($entityId);
$em->flush();

$entity = new sspmod_janus_Model_Entity($entityId, 'idp');
$em->persist($entity);
$em->flush();

$remoteEntityId = new sspmod_janus_Model_Entity_Id('test-sp' . time());
$em->persist($remoteEntityId);
$em->flush();

$remoteEntity = new sspmod_janus_Model_Entity($remoteEntityId, 'sp');
$em->persist($remoteEntity);
$em->flush();

$entityAllowedEntityRelation = new sspmod_janus_Model_Entity_AllowedEntityRelation($entity, $remoteEntityId);
$em->persist($entityAllowedEntityRelation);
$em->flush();
$em->remove($entityAllowedEntityRelation);
$em->flush();

$entityBlockedEntityRelation = new sspmod_janus_Model_Entity_BlockedEntityRelation($entity, $remoteEntityId);
$em->persist($entityBlockedEntityRelation);
$em->flush();
$em->remove($entityBlockedEntityRelation);
$em->flush();

$entityArp = new sspmod_janus_Model_Entity_Arp();
$em->persist($entityArp);
$em->flush();
$em->remove($entityArp);
$em->flush();

$entityDisableConsentRelation = new sspmod_janus_Model_Entity_DisableConsentRelation($entity, $remoteEntityId);
$em->persist($entityDisableConsentRelation);
$em->flush();
$em->remove($entityDisableConsentRelation);
$em->flush();

$entityMetadata = new sspmod_janus_Model_Entity_Metadata($entity, 'testKey', 'testValue');
$em->persist($entityMetadata);
$em->flush();
$em->remove($entityMetadata);
$em->flush();

$em->remove($remoteEntity);
$em->remove($remoteEntityId);
$em->flush();

$userEntityRelation = new sspmod_janus_Model_User_EntityRelation($user, $entity);
$em->persist($userEntityRelation);
$em->flush();
$em->remove($userEntityRelation);
$em->flush();

$em->remove($user);
$em->remove($entity);
$em->remove($entityId);
$em->flush();